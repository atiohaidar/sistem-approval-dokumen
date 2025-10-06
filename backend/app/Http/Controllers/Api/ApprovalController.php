<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    /**
     * Submit document for approval.
     */
    public function submit(Request $request, Document $document): JsonResponse
    {
        // Check if user owns the document
        if ($document->created_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if document is draft
        if (!$document->isDraft()) {
            return response()->json(['message' => 'Document is not in draft status'], 400);
        }

        $request->validate([
            'flow_id' => 'required|exists:approval_flows,id',
        ]);

        try {
            DB::beginTransaction();

            // Update document status
            $document->update([
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Create document approval
            $approval = DocumentApproval::create([
                'document_id' => $document->id,
                'flow_id' => $request->flow_id,
                'status' => 'pending',
                'started_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document submitted for approval successfully',
                'document' => $document->load(['creator', 'template']),
                'approval' => $approval->load(['approvalFlow', 'currentStep']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Document submission failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to submit document'], 500);
        }
    }

    /**
     * Approve document.
     */
    public function approve(Request $request, DocumentApproval $approval): JsonResponse
    {
        return $this->processApprovalAction($request, $approval, 'approve');
    }

    /**
     * Reject document.
     */
    public function reject(Request $request, DocumentApproval $approval): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        return $this->processApprovalAction($request, $approval, 'reject');
    }

    /**
     * Skip approval step.
     */
    public function skip(Request $request, DocumentApproval $approval): JsonResponse
    {
        return $this->processApprovalAction($request, $approval, 'skip');
    }

    /**
     * Delegate approval to another user.
     */
    public function delegate(Request $request, DocumentApproval $approval): JsonResponse
    {
        $request->validate([
            'delegate_to_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        return $this->processApprovalAction($request, $approval, 'delegate', [
            'delegate_to_user_id' => $request->delegate_to_user_id,
        ]);
    }

    /**
     * Add comment to approval.
     */
    public function comment(Request $request, DocumentApproval $approval): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        return $this->processApprovalAction($request, $approval, 'comment', [
            'comment' => $request->comment,
        ]);
    }

    /**
     * Get approval history for a document.
     */
    public function history(Document $document): JsonResponse
    {
        $approvals = $document->approvals()
            ->with(['approvalFlow', 'currentStep', 'approver', 'rejector', 'approvalActions.stepApprover.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($approvals);
    }

    /**
     * Get pending approvals for current user.
     */
    public function pending(): JsonResponse
    {
        $userId = Auth::id();

        // Get all approval steps where user is an approver
        $pendingApprovals = DocumentApproval::where('status', 'in_progress')
            ->whereHas('currentStep.approvers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['document.creator', 'currentStep', 'approvalFlow'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($pendingApprovals);
    }

    /**
     * Process approval action.
     */
    private function processApprovalAction(Request $request, DocumentApproval $approval, string $action, array $extraData = []): JsonResponse
    {
        // Check if approval is in progress
        if (!$approval->isInProgress()) {
            return response()->json(['message' => 'Approval is not in progress'], 400);
        }

        // Debug logging
        Log::info('Processing approval action', [
            'approval_id' => $approval->id,
            'current_step_id' => $approval->current_step_id,
            'user_id' => Auth::id(),
        ]);

        // Find the step approver for current user
        $stepApprover = $approval->currentStep->approvers()
            ->where('user_id', Auth::id())
            ->first();

        if (!$stepApprover) {
            return response()->json(['message' => 'You are not authorized to perform this action'], 403);
        }

        try {
            DB::beginTransaction();

            // Create approval action
            $approvalAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'step_id' => $approval->current_step_id,
                'step_approver_id' => $stepApprover->id,
                'user_id' => Auth::id(),
                'action' => $action,
                'action_data' => array_merge($extraData, [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]),
                'notes' => $request->notes ?? $request->comment ?? null,
            ]);

            // Handle digital signature if provided
            if ($request->has('signature_data')) {
                DigitalSignature::create([
                    'approval_action_id' => $approvalAction->id,
                    'signature_data' => $request->signature_data,
                    'signature_hash' => hash('sha256', $request->signature_data),
                    'verification_status' => 'pending',
                ]);
            }

            // Update approval status based on action
            $this->updateApprovalStatus($approval, $action, $stepApprover);

            DB::commit();

            return response()->json([
                'message' => 'Action processed successfully',
                'approval' => $approval->load(['document', 'currentStep', 'approvalActions']),
                'action' => $approvalAction,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval action failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to process action'], 500);
        }
    }

    /**
     * Update approval status based on action.
     */
    private function updateApprovalStatus(DocumentApproval $approval, string $action, $stepApprover): void
    {
        $currentStep = $approval->currentStep;

        if ($action === 'reject') {
            // Reject the entire approval process
            $approval->update([
                'status' => 'rejected',
                'rejected_by' => Auth::id(),
                'completed_at' => now(),
            ]);

            // Update document status
            $approval->document->update(['status' => 'rejected']);

        } elseif ($action === 'approve') {
            // Check if all required approvals for this step are complete
            $stepActions = ApprovalAction::where('document_approval_id', $approval->id)
                ->whereHas('stepApprover', function ($query) use ($currentStep) {
                    $query->where('approval_step_id', $currentStep->id);
                })
                ->where('action', 'approve')
                ->count();

            $requiredApprovals = $currentStep->isSequential() ? 1 : $currentStep->min_approvals_required;

            if ($stepActions >= $requiredApprovals) {
                // Move to next step or complete approval
                $this->moveToNextStep($approval);
            }
        }
    }

    /**
     * Move approval to next step or complete.
     */
    private function moveToNextStep(DocumentApproval $approval): void
    {
        $currentStepOrder = $approval->current_step_order;
        $nextStep = $approval->approvalFlow->steps()
            ->where('step_order', '>', $currentStepOrder)
            ->orderBy('step_order')
            ->first();

        if ($nextStep) {
            // Move to next step
            $approval->update([
                'current_step_order' => $nextStep->step_order,
                'approval_step_id' => $nextStep->id,
            ]);
        } else {
            // Approval completed
            $approval->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'completed_at' => now(),
            ]);

            // Update document status
            $approval->document->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
