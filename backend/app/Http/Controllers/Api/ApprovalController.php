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

        $documents = Document::whereJsonContains('approvers', $userId)
            ->where('status', 'pending_approval')
            ->whereRaw('current_step < total_steps')
            ->whereRaw('JSON_EXTRACT(approvers, CONCAT("$[", current_step, "]")) = ?', [$userId])
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
     * Approve document and move to next step
     */
    private function approveDocument(Document $document, $user, ?string $comments): void
    {
        // Move to next step
        $document->moveToNextStep();

        // Check if all approvals are complete
        if ($document->current_step >= $document->total_steps) {
            $document->completeApproval();
        }

        // Update QR code with new status
        app(QRCodeService::class)->updateQRCode($document);
    }

    /**
     * Reject document
     */
    private function rejectDocument(Document $document, $user, ?string $comments): void
    {
        $document->rejectApproval();

        // Update QR code with rejected status
        app(QRCodeService::class)->updateQRCode($document);
    }
}
