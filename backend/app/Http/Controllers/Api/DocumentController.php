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
            'approvers' => 'required|array|min:1|max:10', // Max 10 levels
            'approvers.*' => 'required|array|min:1', // Each level must have at least 1 approver
            'approvers.*.*' => 'exists:users,id', // Each approver must be valid user
            'qr_x' => 'required|numeric|min:0|max:1',
            'qr_y' => 'required|numeric|min:0|max:1',
            'qr_page' => 'nullable|integer|min:1',
        ]);

        // Validate QR coordinates
        $this->validateQRCoodinates($request->qr_x, $request->qr_y);

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
        // disini intinya mau ngambil data dokumen serta proses approvalnya sudah sampai mana, siapa saja yang memproses dan hasilnya apa saja
        $document->load(['creator']);
        // mengambil     public function getApprovalProgress():  dari model Document
        $approvalProgress = $document->getApprovalProgress();
        //  mengambil  informasi tambahan tentang approval progressnya, (progress_at dan notes nya  dan info siapa yang sudah memproses)
        // datanya diambil dari tabel document_approvals
        //  mencari document_approvals yang sesuai dengan document id, approver_id, dan level
        //  approval proggres isinya adalah  {
        // "1": {
        //     "status": "completed",
        //     "approved": [
        //         1,
        //         2
        //     ],
        //     "pending": [],
        //     "rejected": []
        // },

        $approvalRecords = DocumentApproval::where('document_id', $document->id)->get();


        return response()->json([
            'document' => $document,
            'approval_progress' => $approvalProgress,
            'approval_records' => $approvalRecords,
        ]);
    }

    /**
     * Calculate approval statistics
     */
    private function calculateApprovalStats(array $approvalProgress): array
    {
        $totalApprovers = collect($approvalProgress)->sum(function ($level) {
            return count($level['approved']) + count($level['pending']) + count($level['rejected'] ?? []);
        });

        $approvedCount = collect($approvalProgress)->sum(function ($level) {
            return count($level['approved']);
        });

        $pendingCount = collect($approvalProgress)->sum(function ($level) {
            return count($level['pending']);
        });

        $rejectedCount = collect($approvalProgress)->sum(function ($level) {
            return count($level['rejected'] ?? []);
        });

        return [
            'total_approvers' => $totalApprovers,
            'approved_count' => $approvedCount,
            'pending_count' => $pendingCount,
            'rejected_count' => $rejectedCount,
            'completion_percentage' => $totalApprovers > 0 ?
                round(($approvedCount / $totalApprovers) * 100, 1) : 0,
        ];
    }

    /**
     * Determine document status based on approval progress
     */
    private function determineDocumentStatus(Document $document, array $stats): string
    {
        if ($stats['rejected_count'] > 0) {
            return 'rejected';
        }

        if ($document->isApproved()) {
            return 'completed';
        }

        if ($stats['pending_count'] > 0) {
            return 'pending_approval';
        }

        return $document->status;
    }

    /**
     * Build workflow information with approvers details
     */
    private function buildWorkflowInfo(Document $document, array $approvalProgress, $approvalRecords): array
    {
        $workflow = [];

        foreach ($document->approvers as $levelIndex => $levelApprovers) {
            $levelNumber = $levelIndex + 1;
            $levelData = $approvalProgress[$levelIndex] ?? ['approved' => [], 'pending' => [], 'rejected' => []];

            $approvers = [];
            foreach ($levelApprovers as $approverId) {
                $approvalRecord = $approvalRecords->get($levelNumber)?->get($approverId)?->first();

                $status = null;
                if (isset($levelData['approved']) && in_array($approverId, $levelData['approved'])) {
                    $status = 'approved';
                } elseif (isset($levelData['rejected']) && in_array($approverId, $levelData['rejected'])) {
                    $status = 'rejected';
                } elseif (isset($levelData['pending']) && in_array($approverId, $levelData['pending'])) {
                    // Only show pending if level is current or document is not rejected
                    if ($levelNumber <= $document->current_level || $document->status !== 'rejected') {
                        $status = 'pending';
                    }
                }

                $approvers[] = [
                    'id' => $approverId,
                    'name' => $approvalRecord?->approver?->name ?? 'Unknown User',
                    'email' => $approvalRecord?->approver?->email ?? null,
                    'status' => $status,
                    'processed_at' => $approvalRecord?->approved_at?->toISOString(),
                    'notes' => $approvalRecord?->notes,
                ];
            }

            $workflow[$levelNumber] = [
                'level' => $levelNumber,
                'status' => $levelData['status'] ?? 'pending',
                'approvers' => $approvers,
                'required_approvals' => count($levelApprovers),
                'approved_count' => count($levelData['approved']),
                'rejected_count' => count($levelData['rejected'] ?? []),
                'pending_count' => count($levelData['pending']),
            ];
        }

        return $workflow;
    }

    /**
     * Find next approver who is pending
     */
    private function findNextApprover(array $workflow): ?array
    {
        foreach ($workflow as $level) {
            foreach ($level['approvers'] as $approver) {
                if ($approver['status'] === 'pending') {
                    return $approver;
                }
            }
        }

        return null;
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
}
