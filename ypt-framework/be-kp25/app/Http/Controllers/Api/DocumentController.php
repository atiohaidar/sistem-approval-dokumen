<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;
use App\Services\PDFWatermarkService;
use App\Services\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['creator:id,name,email', 'template:id,name'])
            ->select(['id', 'title', 'description', 'status', 'created_by', 'template_id', 'created_at', 'updated_at', 'file_path', 'file_name']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by creator if provided
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($documents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Custom validation for approvers (accept string or array)
        $request->merge([
            'approvers' => $this->parseApprovers($request->approvers)
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'template_id' => 'nullable|exists:document_templates,id',
            'approvers' => 'required|array|min:1|max:10', // Max 10 levels
            'approvers.*' => 'required|array|min:1', // Each level must have at least 1 approver
            'approvers.*.*' => 'exists:users,id', // Each approver must be valid user
            'qr_x' => 'required|numeric|min:0|max:1',
            'qr_y' => 'required|numeric|min:0|max:1',
            'qr_page' => 'nullable|integer|min:1',
            'qr_size' => 'nullable|numeric|min:0.05|max:0.5',
        ]);

        // Additional custom validation for approvers structure
        $this->validateApproversStructure($request->approvers, Auth::id());

        // Validate QR coordinates
        $this->validateQRCoodinates($request->qr_x, $request->qr_y);

        $qrSize = $this->normalizeQrSize($request->input('qr_size'));

        $document = DB::transaction(function () use ($request, $qrSize) {
            // Handle file upload
            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            $document = Document::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'template_id' => $request->template_id,
                'status' => 'pending_approval',
                'created_by' => Auth::id(),
                'approvers' => $request->approvers,
                'current_level' => 1, // Start from level 1
                'qr_x' => $request->qr_x,
                'qr_y' => $request->qr_y,
                'qr_page' => $request->qr_page ?? 1,
                'qr_size' => $qrSize,
                'submitted_at' => now(),
            ]);

            // Initialize level progress for the first level
            $document->getLevelProgress();

            // Generate secure access token for the document (expires in 1 year for QR codes)
            $accessService = app(\App\Services\DocumentAccessService::class);
            $accessToken = $accessService->generateToken(
                $document,
                Auth::user(),
                8760, // 1 year expiration for QR code tokens
                ['purpose' => 'QR Code access', 'auto_generated' => true]
            );

            // Generate QR Code with secure token
            $qrPosition = [
                'x' => $request->qr_x,
                'y' => $request->qr_y,
                'page' => $request->qr_page ?? 1,
                'size' => $qrSize,
            ];

            try {
                $qrCodePath = app(QRCodeService::class)->generateForDocument($document, $qrPosition, $accessToken->token);
                // Update document with QR code path
                $document->update(['qr_code_path' => $qrCodePath]);
            } catch (\Exception $e) {
                \Log::error('QR Code generation failed for document ' . $document->id . ': ' . $e->getMessage());
                // Continue without QR code - document is still created
            }

            return $document;
        });

        return response()->json($document->load(['creator', 'template']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document): JsonResponse
    {
        // Load document with related data including approval records
        $document->load([
            'creator:id,name,email,role',
            'template:id,name,description',
            'approvals.approver:id,name,email,role'
        ]);

        // Get approval records for timeline display
        $approvalRecords = $document->approvals()
            ->with(['approver:id,name,email,role'])
            ->orderBy('level')
            ->orderBy('processed_at')
            ->get();

        return response()->json([
            'document' => $document,
            'approval_records' => $approvalRecords,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        // Check if user can update this document
        if ($document->created_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow updates for draft documents
        if (!$document->isDraft()) {
            return response()->json(['message' => 'Cannot update document that is not in draft status'], 422);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'sometimes|file|mimes:pdf|max:10240',
            'template_id' => 'nullable|exists:document_templates,id',
            'public_access' => 'nullable|boolean',
        ]);

        $updateData = $request->only(['title', 'description', 'template_id']);

        if ($request->has('public_access')) {
            $updateData['public_access'] = (bool) $request->input('public_access');
        }

        // Handle file upload if new file is provided
        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                try {
                    Storage::disk('public')->delete($document->file_path);
                } catch (\Exception $e) {
                    \Log::error('Failed to delete old file for document ' . $document->id . ': ' . $e->getMessage());
                    // Continue with update - old file might remain but document is updated
                }
            }

            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
            $updateData['mime_type'] = $file->getMimeType();
        }

        $document->update($updateData);

        return response()->json($document->load(['creator', 'template']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document): JsonResponse
    {
        // Check if user can delete this document
        if ($document->created_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow deletion for draft documents
        if (!$document->isDraft()) {
            return response()->json(['message' => 'Cannot delete document that is not in draft status'], 422);
        }

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            try {
                Storage::disk('public')->delete($document->file_path);
            } catch (\Exception $e) {
                \Log::error('Failed to delete file for document ' . $document->id . ': ' . $e->getMessage());
                // Continue with deletion - file might remain but document is deleted
            }
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Download document with conditional watermark
     */
    public function download(Document $document)
    {
        // Check if user can view this document
        if (!$this->canViewDocument($document, Auth::user())) {
            abort(403, 'Unauthorized');
        }

        $filePath = $document->file_path;
        $fileName = $document->file_name;

        // If not approved, add watermark and QR code
        if (!$document->isApproved()) {
            try {
                $watermarkService = app(PDFWatermarkService::class);
                $qrPosition = [
                    'x' => $document->qr_x,
                    'y' => $document->qr_y,
                    'page' => $document->qr_page ?? 1,
                    'size' => $document->qr_size,
                ];
                $tempPath = $watermarkService->addWatermark($filePath, 'BELUM APPROVE', $document->qr_code_path, $qrPosition);

                // Return watermarked file with QR code
                $fullTempPath = Storage::disk('public')->path($tempPath);

                return response()->download($fullTempPath, $fileName)->deleteFileAfterSend();
            } catch (\Exception $e) {
                // Fallback to original file if watermarking fails
                \Log::error('PDF Watermarking failed: ' . $e->getMessage());
            }
        }

        // If approved, add QR code only
        if ($document->isApproved() && $document->qr_code_path) {
            try {
                $watermarkService = app(PDFWatermarkService::class);
                $qrPosition = [
                    'x' => $document->qr_x,
                    'y' => $document->qr_y,
                    'page' => $document->qr_page ?? 1,
                    'size' => $document->qr_size,
                ];
                $tempPath = $watermarkService->addWatermark($filePath, '', $document->qr_code_path, $qrPosition);

                // Return file with QR code
                $fullTempPath = Storage::disk('public')->path($tempPath);

                return response()->download($fullTempPath, $fileName)->deleteFileAfterSend();
            } catch (\Exception $e) {
                // Fallback to original file if QR code addition fails
                \Log::error('QR Code addition failed: ' . $e->getMessage());
            }
        }

        // Return original file
        $fullPath = Storage::disk('public')->path($filePath);
        return response()->download($fullPath, $fileName);
    }

    /**
     * Get public information about a document (accessible via QR code)
     */
    public function publicInfo(Document $document): JsonResponse
    {
        // Only documents explicitly allowed for public access are served.
        if (empty($document->public_access)) {
            // Return 404 to avoid revealing existence/details for non-public documents
            abort(404, 'Document not available for public access');
        }

        $payload = $this->buildPublicPayload($document);
        return response()->json($payload);
    }

    /**
     * Build the public payload for a document (shared between publicInfo and secureAccess)
     */
    private function buildPublicPayload(Document $document): array
    {
        // Load related data efficiently
        $document->load(['creator']);
        $approvalProgress = $document->getApprovalProgress();

        $approverIds = collect($document->approvers ?? [])
            ->flatten()
            ->filter()
            ->unique();

        $approverMap = User::whereIn('id', $approverIds)
            ->get(['id', 'name', 'email', 'role'])
            ->keyBy('id');

        $approvalLevels = [];

        foreach (($document->approvers ?? []) as $index => $levelApprovers) {
            $levelNumber = $index + 1;
            $levelProgress = $approvalProgress[$levelNumber] ?? [
                'status' => 'pending',
                'approved' => [],
                'pending' => [],
                'rejected' => [],
            ];

            $approverDetails = [];
            foreach ($levelApprovers as $approverId) {
                $user = $approverMap->get($approverId);

                $approverStatus = match (true) {
                    in_array($approverId, $levelProgress['approved'] ?? []) => 'approved',
                    in_array($approverId, ($levelProgress['rejected'] ?? [])) => 'rejected',
                    default => match ($levelProgress['status'] ?? 'pending') {
                            'completed' => 'approved',
                            'rejected' => 'skipped',
                            'cancelled' => 'cancelled',
                            default => 'pending',
                        },
                };

                $approverDetails[] = [
                    'id' => $approverId,
                    'user' => $user ? $user->only(['id', 'name', 'email', 'role']) : null,
                    'status' => $approverStatus,
                ];
            }

            $approvalLevels[$levelNumber] = [
                'status' => $levelProgress['status'] ?? 'pending',
                'approvers' => $approverDetails,
            ];
        }

        $approvalRecords = DocumentApproval::where('document_id', $document->id)
            ->with(['approver:id,name,email,role'])
            ->get();

        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        $frontendPublicUrl = $frontendBase . '/public/' . $document->id;

        return [
            'document' => $document,
            // backend API public url (existing)
            'public_url' => url("/api/documents/{$document->id}/public-info"),
            // frontend page that shows public info (for QR and user-facing links)
            'frontend_url' => $frontendPublicUrl,
            'preview_url' => url("/api/documents/{$document->id}/public-preview"),
            'approval_progress' => $approvalProgress,
            'approval_levels' => $approvalLevels,
            'approval_records' => $approvalRecords,
        ];
    }

    /**
     * Stream document for public preview (used by QR landing page)
     */
    public function publicPreview(Document $document)
    {
        // Only serve public preview for documents explicitly allowed
        if (empty($document->public_access)) {
            abort(404, 'Document not available for public access');
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $fileName = $document->file_name ?? 'document.pdf';
        $qrPosition = [
            'x' => $document->qr_x,
            'y' => $document->qr_y,
            'page' => $document->qr_page ?? 1,
            'size' => $document->qr_size,
        ];

        try {
            $watermarkService = app(PDFWatermarkService::class);
            $watermarkText = $document->isApproved() ? '' : 'BELUM APPROVE';
            $tempPath = $watermarkService->addWatermark(
                $document->file_path,
                $watermarkText,
                $document->qr_code_path,
                $qrPosition
            );

            $fullTempPath = Storage::disk('public')->path($tempPath);

            $response = response()->file($fullTempPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ])->deleteFileAfterSend(true);

            // Allow embedding this preview only into trusted frontend origin(s).
            // By default Laravel's FrameGuard middleware sets X-Frame-Options: SAMEORIGIN
            // which prevents framing from a different origin (e.g. frontend at localhost:3000).
            // Remove the X-Frame-Options header for this response and set a restrictive
            // Content-Security-Policy frame-ancestors directive allowing the configured
            // frontend URL plus self. This is safer than allowing all origins.
            $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
            $frontendBase = rtrim($frontendBase, '/');

            // Remove header that would block cross-origin framing
            $response->headers->remove('X-Frame-Options');
            // Set frame-ancestors to allow self and the configured frontend origin
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' {$frontendBase};");

            return $response;
        } catch (\Exception $e) {
            \Log::error('Public preview failed for document ' . $document->id . ': ' . $e->getMessage());
        }

        $fullPath = Storage::disk('public')->path($document->file_path);

        $response = response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);

        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' {$frontendBase};");

        return $response;
    }

    /**
     * Check if user can view/download the document
     */
    private function canViewDocument(Document $document, $user): bool
    {
        // Creator can always view
        if ($document->created_by === $user->id) {
            return true;
        }

        // Approvers can view documents they're assigned to (check all levels)
        if ($document->approvers && is_array($document->approvers)) {
            foreach ($document->approvers as $levelApprovers) {
                if (is_array($levelApprovers) && in_array($user->id, $levelApprovers)) {
                    return true;
                }
            }
        }

        // Admins can view all documents
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Parse approvers input - accept both string and array
     */
    private function parseApprovers($approvers): array
    {
        if (is_string($approvers)) {
            // Try to decode JSON string
            $decoded = json_decode($approvers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // If not valid JSON, try to parse as comma-separated string
            $parsed = array_map('trim', explode(',', trim($approvers, '[]')));
            $filtered = array_filter($parsed, function ($item) {
                return is_numeric($item) && (int) $item > 0;
            });

            if (empty($filtered)) {
                throw new \InvalidArgumentException('Invalid approvers format. Must be valid JSON array or comma-separated user IDs.');
            }

            return $filtered;
        }

        return (array) $approvers;
    }

    /**
     * Validate QR coordinates
     */
    private function validateQRCoodinates(float $x, float $y): void
    {
        if (!is_numeric($x) || $x < 0 || $x > 1) {
            throw new \InvalidArgumentException('QR x coordinate must be between 0.0 and 1.0');
        }

        if (!is_numeric($y) || $y < 0 || $y > 1) {
            throw new \InvalidArgumentException('QR y coordinate must be between 0.0 and 1.0');
        }
    }

    /**
     * Validate approvers structure for duplicates and business rules
     */
    private function validateApproversStructure(array $approvers, int $creatorId): void
    {
        foreach ($approvers as $levelIndex => $levelApprovers) {
            // Check for duplicates within the same level
            if (count($levelApprovers) !== count(array_unique($levelApprovers))) {
                throw new \InvalidArgumentException("Level " . ($levelIndex + 1) . " contains duplicate approvers.");
            }
        }
    }

    /**
     * Normalize QR size coming from the request to a safe range.
     */
    private function normalizeQrSize($size): float
    {
        $defaultSize = 50 / 210; // ≈0.238 – aligns with frontend default (50mm on A4 width)
        $minSize = 0.05;
        $maxSize = 0.5;

        if ($size === null) {
            return $defaultSize;
        }

        if (!is_numeric($size)) {
            return $defaultSize;
        }

        $numericSize = (float) $size;
        if (!is_finite($numericSize)) {
            return $defaultSize;
        }

        return max($minSize, min($maxSize, $numericSize));
    }

    /**
     * Generate a secure access token for a document
     */
    public function generateAccessToken(Request $request, Document $document): JsonResponse
    {
        $user = Auth::user();
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Check authorization
        if (!$accessService->canGenerateToken($document, $user)) {
            return response()->json([
                'message' => 'Unauthorized to generate access token for this document'
            ], 403);
        }

        $request->validate([
            'expires_in_hours' => 'nullable|integer|min:1|max:8760', // Max 1 year
            'purpose' => 'nullable|string|max:255',
        ]);

        $expiresInHours = $request->input('expires_in_hours', 24);
        $metadata = [
            'purpose' => $request->input('purpose', 'Document access'),
            'generated_for' => $request->input('generated_for'),
        ];

        $token = $accessService->generateToken($document, $user, $expiresInHours, $metadata);

        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        $accessUrl = $frontendBase . '/secure/' . $token->token;

        return response()->json([
            'token_id' => $token->id,
            'access_url' => $accessUrl,
            'expires_at' => $token->expires_at,
            'expires_in_hours' => $expiresInHours,
        ], 201);
    }

    /**
     * Revoke a specific access token
     */
    public function revokeAccessToken(Request $request, Document $document, int $tokenId): JsonResponse
    {
        $user = Auth::user();
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Find the token
        $token = \App\Models\DocumentAccessToken::where('id', $tokenId)
            ->where('document_id', $document->id)
            ->first();

        if (!$token) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        // Check authorization - only token creator, document creator, or admin can revoke
        if (
            $token->generated_by !== $user->id &&
            $document->created_by !== $user->id &&
            !$user->isAdmin()
        ) {
            return response()->json([
                'message' => 'Unauthorized to revoke this token'
            ], 403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $accessService->revokeToken($token, $request->input('reason'));

        return response()->json([
            'message' => 'Token revoked successfully',
            'token_id' => $tokenId,
        ]);
    }

    /**
     * Rotate an access token (generate new, revoke old)
     */
    public function rotateAccessToken(Request $request, Document $document, int $tokenId): JsonResponse
    {
        $user = Auth::user();
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Find the token
        $oldToken = \App\Models\DocumentAccessToken::where('id', $tokenId)
            ->where('document_id', $document->id)
            ->first();

        if (!$oldToken) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        // Check authorization
        if (
            $oldToken->generated_by !== $user->id &&
            $document->created_by !== $user->id &&
            !$user->isAdmin()
        ) {
            return response()->json([
                'message' => 'Unauthorized to rotate this token'
            ], 403);
        }

        $request->validate([
            'expires_in_hours' => 'nullable|integer|min:1|max:8760',
        ]);

        $expiresInHours = $request->input('expires_in_hours', 24);
        $newToken = $accessService->rotateToken($oldToken, $expiresInHours);

        $frontendBase = env('FRONTEND_URL', 'http://localhost:3000');
        $frontendBase = rtrim($frontendBase, '/');
        $accessUrl = $frontendBase . '/secure/' . $newToken->token;

        return response()->json([
            'token_id' => $newToken->id,
            'access_url' => $accessUrl,
            'expires_at' => $newToken->expires_at,
            'old_token_id' => $tokenId,
            'message' => 'Token rotated successfully',
        ]);
    }

    /**
     * Set or unset public access for a document. Only creator or admin can perform this.
     */
    public function setPublicAccess(Request $request, Document $document): JsonResponse
    {
        $user = Auth::user();

        // Authorization: only creator or admin
        if ($document->created_by !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized to change public access'], 403);
        }

        $request->validate([
            'public_access' => 'required|boolean',
        ]);

        $document->public_access = (bool) $request->input('public_access');
        $document->save();

        return response()->json([
            'message' => 'Public access updated',
            'public_access' => $document->public_access,
            'document' => $document,
        ]);
    }

    /**
     * Get active tokens for a document
     */
    public function getAccessTokens(Document $document): JsonResponse
    {
        $user = Auth::user();
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Check authorization
        if (!$accessService->canGenerateToken($document, $user)) {
            return response()->json([
                'message' => 'Unauthorized to view access tokens for this document'
            ], 403);
        }

        $tokens = $accessService->getActiveTokens($document);

        return response()->json([
            'tokens' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'generated_by' => $token->generatedBy,
                    'expires_at' => $token->expires_at,
                    'access_count' => $token->access_count,
                    'last_accessed_at' => $token->last_accessed_at,
                    'metadata' => $token->metadata,
                    'created_at' => $token->created_at,
                ];
            }),
        ]);
    }

    /**
     * Secure document access using token
     */
    public function secureAccess(Request $request, string $token): JsonResponse
    {
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Validate token
        $accessToken = $accessService->validateToken($token);

        if (!$accessToken) {
            // Log failed access attempt without document
            $this->logFailedAccess('access_attempt', 'Invalid or expired token');

            return response()->json([
                'message' => 'Invalid or expired access token'
            ], 403);
        }

        $document = $accessToken->document;

        // Log successful access
        $accessService->logAccess(
            $document,
            'view',
            $accessToken,
            Auth::user()
        );

        // Build the same payload as the public info endpoint but bypass the
        // public_access restriction because this is an authenticated token access.
        $payload = $this->buildPublicPayload($document);
        $payload['token_expires_at'] = $accessToken->expires_at;

        return response()->json($payload);
    }

    /**
     * Secure document preview using token
     */
    public function securePreview(Request $request, string $token)
    {
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Validate token
        $accessToken = $accessService->validateToken($token);

        if (!$accessToken) {
            // Log failed access attempt
            $this->logFailedAccess('preview_attempt', 'Invalid or expired token');
            abort(403, 'Invalid or expired access token');
        }

        $document = $accessToken->document;

        // Log successful preview access
        $accessService->logAccess(
            $document,
            'preview',
            $accessToken,
            Auth::user()
        );

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $fileName = $document->file_name ?? 'document.pdf';
        $qrPosition = [
            'x' => $document->qr_x,
            'y' => $document->qr_y,
            'page' => $document->qr_page ?? 1,
            'size' => $document->qr_size,
        ];

        try {
            $watermarkService = app(PDFWatermarkService::class);
            $watermarkText = $document->isApproved() ? '' : 'BELUM APPROVE';
            $tempPath = $watermarkService->addWatermark(
                $document->file_path,
                $watermarkText,
                $document->qr_code_path,
                $qrPosition
            );

            $fullTempPath = Storage::disk('public')->path($tempPath);

            return response()->file($fullTempPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Secure preview failed for document ' . $document->id . ': ' . $e->getMessage());
        }

        $fullPath = Storage::disk('public')->path($document->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Secure document download using token
     */
    public function secureDownload(Request $request, string $token)
    {
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Validate token
        $accessToken = $accessService->validateToken($token);

        if (!$accessToken) {
            // Log failed access attempt
            $this->logFailedAccess('download_attempt', 'Invalid or expired token');
            abort(403, 'Invalid or expired access token');
        }

        $document = $accessToken->document;

        // Log successful download access
        $accessService->logAccess(
            $document,
            'download',
            $accessToken,
            Auth::user()
        );

        $filePath = $document->file_path;
        $fileName = $document->file_name;

        // If not approved, add watermark and QR code
        if (!$document->isApproved()) {
            try {
                $watermarkService = app(PDFWatermarkService::class);
                $qrPosition = [
                    'x' => $document->qr_x,
                    'y' => $document->qr_y,
                    'page' => $document->qr_page ?? 1,
                    'size' => $document->qr_size,
                ];
                $tempPath = $watermarkService->addWatermark($filePath, 'BELUM APPROVE', $document->qr_code_path, $qrPosition);

                $fullTempPath = Storage::disk('public')->path($tempPath);

                return response()->download($fullTempPath, $fileName)->deleteFileAfterSend();
            } catch (\Exception $e) {
                \Log::error('PDF Watermarking failed: ' . $e->getMessage());
            }
        }

        // If approved, add QR code only
        if ($document->isApproved() && $document->qr_code_path) {
            try {
                $watermarkService = app(PDFWatermarkService::class);
                $qrPosition = [
                    'x' => $document->qr_x,
                    'y' => $document->qr_y,
                    'page' => $document->qr_page ?? 1,
                    'size' => $document->qr_size,
                ];
                $tempPath = $watermarkService->addWatermark($filePath, '', $document->qr_code_path, $qrPosition);

                $fullTempPath = Storage::disk('public')->path($tempPath);

                return response()->download($fullTempPath, $fileName)->deleteFileAfterSend();
            } catch (\Exception $e) {
                \Log::error('QR Code addition failed: ' . $e->getMessage());
            }
        }

        // Return original file
        $fullPath = Storage::disk('public')->path($filePath);
        return response()->download($fullPath, $fileName);
    }

    /**
     * Get access audit logs for a document
     */
    public function getAccessLogs(Request $request, Document $document): JsonResponse
    {
        $user = Auth::user();
        $accessService = app(\App\Services\DocumentAccessService::class);

        // Check authorization - only document creator or admin
        if ($document->created_by !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view access logs for this document'
            ], 403);
        }

        $hours = $request->input('hours', 24);
        $stats = $accessService->getAccessStats($document, $hours);

        $logs = \App\Models\AccessAuditLog::where('document_id', $document->id)
            ->recent($hours)
            ->with(['user:id,name,email', 'accessToken:id,expires_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'stats' => $stats,
            'logs' => $logs,
        ]);
    }

    /**
     * Helper method to log failed access attempts
     */
    private function logFailedAccess(string $action, string $reason): void
    {
        \App\Models\AccessAuditLog::create([
            'document_id' => null,
            'access_token_id' => null,
            'user_id' => Auth::id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => false,
            'failure_reason' => $reason,
        ]);
    }
}
