<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StepApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'step_id',
        'user_id',
        'is_backup',
    ];

    protected $casts = [
        'is_backup' => 'boolean',
    ];

    /**
     * Get the approval step this approver belongs to.
     */
    public function approvalStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalStep::class, 'step_id');
    }

    /**
     * Get the user who is the approver.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all approval actions by this approver.
     */
    public function approvalActions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }
}
