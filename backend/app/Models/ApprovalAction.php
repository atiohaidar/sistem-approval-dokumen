<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_approval_id',
        'step_id',
        'step_approver_id',
        'user_id',
        'action', // 'approve', 'reject', 'skip', 'delegate', 'comment'
        'action_data', // JSON data for additional information
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'action_data' => 'array',
    ];

    /**
     * Get the document approval this action belongs to.
     */
    public function documentApproval(): BelongsTo
    {
        return $this->belongsTo(DocumentApproval::class);
    }

    /**
     * Get the step approver who performed this action.
     */
    public function stepApprover(): BelongsTo
    {
        return $this->belongsTo(StepApprover::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'step_approver_id', 'id');
    }

    /**
     * Get the digital signature for this action if exists.
     */
    public function digitalSignature(): BelongsTo
    {
        return $this->belongsTo(DigitalSignature::class);
    }

    /**
     * Check if action is approve.
     */
    public function isApprove(): bool
    {
        return $this->action === 'approve';
    }

    /**
     * Check if action is reject.
     */
    public function isReject(): bool
    {
        return $this->action === 'reject';
    }

    /**
     * Check if action is skip.
     */
    public function isSkip(): bool
    {
        return $this->action === 'skip';
    }

    /**
     * Check if action is delegate.
     */
    public function isDelegate(): bool
    {
        return $this->action === 'delegate';
    }

    /**
     * Check if action is comment.
     */
    public function isComment(): bool
    {
        return $this->action === 'comment';
    }
}
