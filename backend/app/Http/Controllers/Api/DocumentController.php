<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Services\PDFWatermarkService;
use App\Services\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['creator', 'template']);

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
            'approvers' => 'required|array|min:1|max:10',
            'approvers.*' => 'exists:users,id|different:created_by',
            'qr_position' => 'required',
        ]);

        // Validate and parse QR position (support both old and new format)
        $qrPosition = $this->parseQRPosition($request->qr_position);
        $request->merge(['qr_position_parsed' => $qrPosition]);

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
            'qr_position' => $qrPosition, // Keep for backward compatibility
            'qr_x' => $qrPosition['x'] ?? null,
            'qr_y' => $qrPosition['y'] ?? null,
            'qr_page' => $qrPosition['page'] ?? 1,
            'total_steps' => count($request->approvers),
            'submitted_at' => now(),
        ]);

        // Generate QR Code
        $qrCodePath = app(QRCodeService::class)->generateForDocument($document, $qrPosition);

        // Update document with QR code path
        $document->update(['qr_code_path' => $qrCodePath]);

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
                Storage::disk('public')->delete($document->file_path);
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
            Storage::disk('public')->delete($document->file_path);
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
                $tempPath = $watermarkService->addWatermark($filePath, 'BELUM APPROVE', $document->qr_code_path, $document->qr_position);

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
                $tempPath = $watermarkService->addWatermark($filePath, '', $document->qr_code_path, $document->qr_position);

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
        // Load related data
        $document->load(['creator']);

        // Get approval progress - since we don't have approval records, we'll simulate based on current_step
        $approvedCount = $document->current_step;
        $pendingCount = max(0, $document->total_steps - $document->current_step);
        $rejectedCount = 0; // We don't track rejections in this simplified system

        // Build approvers info
        $approversInfo = collect($document->approvers)->map(function ($approverId, $index) use ($document) {
            $user = User::find($approverId); // We need to load user data

            $status = 'pending';
            if ($index < $document->current_step) {
                $status = 'approved';
            } elseif ($index === $document->current_step && $document->isPendingApproval()) {
                $status = 'pending';
            }

            return [
                'id' => $approverId,
                'name' => $user ? $user->name : 'Unknown User',
                'email' => $user ? $user->email : null,
                'status' => $status,
                'approved_at' => null, // We don't track individual approval times
                'notes' => null, // We don't have approval notes
            ];
        });

        // Calculate current step
        $currentStep = null;
        if ($document->isPendingApproval() && $document->current_step < $document->total_steps) {
            $currentStep = $document->current_step + 1;
        }

        $response = [
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'status' => $document->status,
                'file_name' => $document->file_name,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type,
                'created_at' => $document->created_at,
                'submitted_at' => $document->submitted_at,
                'creator' => [
                    'id' => $document->creator->id,
                    'name' => $document->creator->name,
                    'email' => $document->creator->email,
                ],
            ],
            'approvers' => $approversInfo,
            'progress' => [
                'total_approvers' => $document->total_steps,
                'approved_count' => $approvedCount,
                'pending_count' => $pendingCount,
                'rejected_count' => $rejectedCount,
                'current_step' => $currentStep,
                'completion_percentage' => $document->total_steps > 0 ?
                    round(($approvedCount / $document->total_steps) * 100, 1) : 0,
            ],
            'workflow' => [
                'is_sequential' => true,
                'can_download' => $document->isApproved(),
                'next_approver' => $approversInfo->firstWhere('status', 'pending'),
            ],
        ];

        return response()->json($response);
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

        // Approvers can view documents they're assigned to
        if ($document->approvers && in_array($user->id, $document->approvers)) {
            return true;
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
            return array_filter($parsed, function($item) {
                return is_numeric($item) && (int)$item > 0;
            });
        }

        return (array) $approvers;
    }

    /**
     * Parse QR position - support both old string format and new coordinate format
     */
    private function parseQRPosition($qrPosition): array
    {
        // If it's an array/object (new format)
        if (is_array($qrPosition)) {
            $this->validateCoordinateFormat($qrPosition);
            return [
                'x' => $qrPosition['x'] ?? null,
                'y' => $qrPosition['y'] ?? null,
                'page' => $qrPosition['page'] ?? 1,
            ];
        }

        // If it's a string, check if it's old format or JSON
        if (is_string($qrPosition)) {
            // Try to decode as JSON first (new format sent as string)
            $decoded = json_decode($qrPosition, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->validateCoordinateFormat($decoded);
                return [
                    'x' => $decoded['x'] ?? null,
                    'y' => $decoded['y'] ?? null,
                    'page' => $decoded['page'] ?? 1,
                ];
            }

            // Old string format - convert to coordinates
            return $this->convertOldPositionToCoordinates($qrPosition);
        }

        throw new \InvalidArgumentException('Invalid QR position format');
    }

    /**
     * Validate coordinate format
     */
    private function validateCoordinateFormat(array $coords): void
    {
        if (isset($coords['x']) && (!is_numeric($coords['x']) || $coords['x'] < 0 || $coords['x'] > 1)) {
            throw new \InvalidArgumentException('QR x coordinate must be between 0.0 and 1.0');
        }

        if (isset($coords['y']) && (!is_numeric($coords['y']) || $coords['y'] < 0 || $coords['y'] > 1)) {
            throw new \InvalidArgumentException('QR y coordinate must be between 0.0 and 1.0');
        }

        if (isset($coords['page']) && (!is_int($coords['page']) || $coords['page'] < 1)) {
            throw new \InvalidArgumentException('QR page must be a positive integer');
        }
    }

    /**
     * Convert old position format to coordinates
     */
    private function convertOldPositionToCoordinates(string $position): array
    {
        $coordinates = [
            'top-left' => ['x' => 0.1, 'y' => 0.1],
            'top-right' => ['x' => 0.8, 'y' => 0.1],
            'bottom-left' => ['x' => 0.1, 'y' => 0.8],
            'bottom-right' => ['x' => 0.8, 'y' => 0.8],
        ];

        if (!isset($coordinates[$position])) {
            throw new \InvalidArgumentException('Invalid QR position: ' . $position);
        }

        return array_merge($coordinates[$position], ['page' => 1]);
    }
}
