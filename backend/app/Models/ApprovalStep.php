<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'step_order',
        'step_name',
        'step_type',
        'is_required',
        'can_skip',
        'minimum_approvers',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'minimum_approvers' => 'integer',
        'is_required' => 'boolean',
        'can_skip' => 'boolean',
    ];

    /**
     * Get the approval flow this step belongs to.
     */
    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    /**
     * Get all approvers for this step.
     */
    public function approvers(): HasMany
    {
        return $this->hasMany(StepApprover::class, 'step_id')->orderBy('approver_order');
    }

    /**
     * Get all document approvals for this step.
     */
    public function documentApprovals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Check if this step requires sequential approval.
     */
    public function isSequential(): bool
    {
        return $this->step_type === 'sequential';
    }

    /**
     * Check if this step requires parallel approval.
     */
    public function isParallel(): bool
    {
        return $this->step_type === 'parallel';
    }

    /**
     * Get the name attribute (alias for step_name).
     */
    public function getNameAttribute(): string
    {
        return $this->step_name;
    }
}
