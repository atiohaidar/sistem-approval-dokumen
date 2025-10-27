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
        ]);

        // Additional custom validation for approvers structure
        $this->validateApproversStructure($request->approvers, Auth::id());

        // Validate QR coordinates
        $this->validateQRCoodinates($request->qr_x, $request->qr_y);

        $document = DB::transaction(function () use ($request) {
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
                'submitted_at' => now(),
            ]);

            // Initialize level progress for the first level
            $document->getLevelProgress();

            // Generate QR Code
            $qrPosition = [
                'x' => $request->qr_x,
                'y' => $request->qr_y,
                'page' => $request->qr_page ?? 1,
            ];

            try {
                $qrCodePath = app(QRCodeService::class)->generateForDocument($document, $qrPosition);
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
        return response()->json($document->load(['creator', 'template']));
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
        ]);

        $updateData = $request->only(['title', 'description', 'template_id']);

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

        return response()->json([
            'document' => $document,
            // backend API public url (existing)
            'public_url' => url("/api/documents/{$document->id}/public-info"),
            // frontend page that shows public info (for QR and user-facing links)
            'frontend_url' => $frontendPublicUrl,
            'preview_url' => url("/api/documents/{$document->id}/public-preview"),
            'approval_progress' => $approvalProgress,
            'approval_levels' => $approvalLevels,
            'approval_records' => $approvalRecords,
        ]);
    }

    /**
     * Stream document for public preview (used by QR landing page)
     */
    public function publicPreview(Document $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document file not found');
        }

        $fileName = $document->file_name ?? 'document.pdf';
        $qrPosition = [
            'x' => $document->qr_x,
            'y' => $document->qr_y,
            'page' => $document->qr_page ?? 1,
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
            \Log::error('Public preview failed for document ' . $document->id . ': ' . $e->getMessage());
        }

        $fullPath = Storage::disk('public')->path($document->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Calculate approval statistics
     */
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
            $filtered = array_filter($parsed, function($item) {
                return is_numeric($item) && (int)$item > 0;
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

            // Optional: Prevent creator from being an approver (uncomment if needed)
            // if (in_array($creatorId, $levelApprovers)) {
            //     throw new \InvalidArgumentException("Creator cannot be an approver.");
            // }
        }
    }
}
