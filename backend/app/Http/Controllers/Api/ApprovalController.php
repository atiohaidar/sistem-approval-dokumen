<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    /**
     * Get pending approvals for current user
     */
    public function getPendingApprovals(): JsonResponse
    {
        $userId = Auth::id();

        $documents = Document::where('status', 'pending_approval')
            ->whereRaw('JSON_EXTRACT(approvers, CONCAT("$[", current_level - 1, "]")) LIKE ?', ["%{$userId}%"])
            ->whereRaw('JSON_EXTRACT(level_progress, "$.pending") LIKE ?', ["%{$userId}%"])
            ->with(['creator'])
            ->get();

        return response()->json($documents);
    }

    /**
     * Process approval action (approve/reject)
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
            return response()->json([
                'message' => 'You are not authorized to approve this document at this time.'
            ], 403);
        }

        if ($request->action === 'approve') {
            $this->approveDocument($document, $user, $request->comments);
        } else {
            $this->rejectDocument($document, $user, $request->comments);
        }

        return response()->json(['message' => 'Approval processed successfully']);
    }

    /**
     * Delegate approval to another user
     */
    public function delegateApproval(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'delegate_to' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $delegateTo = $request->delegate_to;

        // Cannot delegate to yourself
        if ($delegateTo == $user->id) {
            return response()->json([
                'message' => 'You cannot delegate approval to yourself.'
            ], 422);
        }

        // Check if user can approve this document
        if (!$document->canBeApprovedBy($user->id)) {
            return response()->json([
                'message' => 'You are not authorized to delegate approval for this document.'
            ], 403);
        }

        // Check if delegate_to user is also in the current level
        $currentApprovers = $document->getCurrentApproverIds();
        if (!in_array($delegateTo, $currentApprovers)) {
            return response()->json([
                'message' => 'Delegate user must be in the same approval level.'
            ], 400);
        }

        // Update level progress: remove current user from pending, add delegate_to if not already approved
        $progress = $document->getLevelProgress();
        $progress['pending'] = array_diff($progress['pending'], [$user->id]);

        // Add delegate_to to pending if not already approved
        if (!in_array($delegateTo, $progress['approved'])) {
            $progress['pending'][] = $delegateTo;
        }

        $document->level_progress = $progress;
        $document->save();

        return response()->json(['message' => 'Approval delegated successfully']);
    }

    /**
     * Approve document and move to next level if needed
     */
    private function approveDocument(Document $document, $user, ?string $comments): void
    {
        $document->approveByUser($user->id);

        // Update QR code with new status
        app(QRCodeService::class)->updateQRCode($document);
    }

    /**
     * Reject document
     */
    private function rejectDocument(Document $document, $user, ?string $comments): void
    {
        $document->rejectByUser($user->id);

        // Update QR code with rejected status
        app(QRCodeService::class)->updateQRCode($document);
    }
}
