<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;

class ApprovalService
{
    /**
     * Process document approval
     */
    public function approveDocument(Document $document, User $user, ?string $comments = null): void
    {
        // Create approval record first
        $this->createApprovalRecord($document, $user, 'approved', $comments);

        // Update document status
        $document->approveByUser($user->id);

        // Update QR code with new status
        app(QRCodeService::class)->updateQRCode($document);
    }

    /**
     * Process document rejection
     */
    public function rejectDocument(Document $document, User $user, ?string $comments = null): void
    {
        // Create approval record first
        $this->createApprovalRecord($document, $user, 'rejected', $comments);

        // Update document status
        $document->rejectByUser($user->id);

        // Update QR code with rejected status
        app(QRCodeService::class)->updateQRCode($document);
    }

    /**
     * Delegate approval to another user
     */
    public function delegateApproval(Document $document, User $user, int $delegateTo): void
    {
        // Validate delegation
        $this->validateDelegation($document, $user, $delegateTo);

        // Update level progress: remove current user from pending, add delegate_to if not already approved
        $progress = $document->getLevelProgress();

        // Remove current user from pending
        if (($key = array_search($user->id, $progress['pending'])) !== false) {
            unset($progress['pending'][$key]);
            $progress['pending'] = array_values($progress['pending']); // Reindex
        }

        // Add delegate_to to pending if not already approved
        if (!in_array($delegateTo, $progress['approved'])) {
            $progress['pending'][] = $delegateTo;
        }

        $document->level_progress = $progress;
        $document->save();
    }

    /**
     * Validate delegation requirements
     */
    private function validateDelegation(Document $document, User $user, int $delegateTo): void
    {
        // Cannot delegate to yourself
        if ($delegateTo == $user->id) {
            throw new \InvalidArgumentException('You cannot delegate approval to yourself.');
        }

        // Check if user can approve this document
        if (!$document->canBeApprovedBy($user->id)) {
            throw new \InvalidArgumentException('You are not authorized to delegate approval for this document.');
        }

        // Check if delegate_to user is also in the current level
        $currentApprovers = $document->getCurrentApproverIds();
        if (!in_array($delegateTo, $currentApprovers)) {
            throw new \InvalidArgumentException('Delegate user must be in the same approval level.');
        }
    }

    /**
     * Create approval record for audit trail
     */
    private function createApprovalRecord(Document $document, User $user, string $action, ?string $comments): void
    {
        DocumentApproval::create([
            'document_id' => $document->id,
            'approver_id' => $user->id,
            'action' => $action,
            'notes' => $comments,
            'level' => $document->current_level,
            'processed_at' => now(),
        ]);
    }
}