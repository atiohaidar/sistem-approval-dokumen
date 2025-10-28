<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Document;
use App\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Approval Controller
 * 
 * Handles document approval workflow operations
 */
class ApprovalController extends Controller
{
    use ApiResponse;

    protected ApprovalService $approvalService;

    // Constants for array indexing (since arrays are 0-based)
    private const LEVEL_INDEX_OFFSET = 1;

    /**
     * Get the array index for current approval level
     * Since database levels are 1-based but arrays are 0-based
     */
    private function getCurrentLevelIndex(Document $document): int
    {
        return $document->current_level - self::LEVEL_INDEX_OFFSET;
    }

    /**
     * Get approval service instance
     */
    private function getApprovalService(): ApprovalService
    {
        return app(ApprovalService::class);
    }

    /**
     * Get pending approvals for current user
     *
     * @return JsonResponse
     */
    public function getPendingApprovals(): JsonResponse
    {
        $userId = Auth::id();

        // Use SQLite-compatible JSON query
        $documents = Document::where('status', 'pending_approval')
            ->where(function ($query) use ($userId) {
                // Check if user is in approvers array (any level)
                $query->whereRaw('json_extract(approvers, "$") LIKE ?', ["%{$userId}%"])
                      ->whereRaw('json_extract(level_progress, "$.pending") LIKE ?', ["%{$userId}%"]);
            })
            ->with(['creator'])
            ->get();

        return $this->successResponse($documents, 'Pending approvals retrieved successfully');
    }

    /**
     * Process approval action (approve/reject)
     *
     * @param Request $request
     * @param Document $document
     * @return JsonResponse
     */
    public function processApproval(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Check if user is current approver
        if (!$document->canBeApprovedBy($user->id)) {
            return $this->unauthorizedResponse('You are not authorized to approve this document at this time.');
        }

        try {
            if ($request->action === 'approve') {
                $this->getApprovalService()->approveDocument($document, $user, $request->comments);
                $message = 'Document approved successfully';
            } else {
                $this->getApprovalService()->rejectDocument($document, $user, $request->comments);
                $message = 'Document rejected successfully';
            }

            // Log successful approval action for audit trail
            \Log::info('Document approval processed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'document_id' => $document->id,
                'action' => $request->action,
                'level' => $document->current_level,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->successResponse(null, $message);
        } catch (\Exception $e) {
            // Log failed approval attempt
            \Log::warning('Document approval failed', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'action' => $request->action,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return $this->errorResponse('Failed to process approval: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delegate approval to another user
     *
     * @param Request $request
     * @param Document $document
     * @return JsonResponse
     */
    public function delegateApproval(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'delegate_to' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $delegateTo = $request->delegate_to;

        try {
            $this->getApprovalService()->delegateApproval($document, $user, $delegateTo);

            // Log successful delegation
            \Log::info('Document approval delegated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'document_id' => $document->id,
                'delegate_to_user_id' => $delegateTo,
                'level' => $document->current_level,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->successResponse(null, 'Approval delegated successfully');
        } catch (\InvalidArgumentException $e) {
            // Log validation error
            \Log::warning('Document delegation validation failed', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'delegate_to_user_id' => $delegateTo,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            // Map validation errors to appropriate HTTP status codes
            $message = $e->getMessage();
            if (str_contains($message, 'delegate approval to yourself')) {
                return $this->validationErrorResponse(['delegate_to' => [$message]], $message);
            }
            return $this->errorResponse($message, 400);
        } catch (\Exception $e) {
            // Log system error
            \Log::error('Document delegation failed', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'delegate_to_user_id' => $delegateTo,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return $this->errorResponse('Failed to delegate approval: ' . $e->getMessage(), 500);
        }
    }
}
