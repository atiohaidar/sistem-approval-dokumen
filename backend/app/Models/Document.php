<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'template_id',
        'status',
        'created_by',
        'submitted_at',
        'completed_at',
        'current_step',
        'total_steps',
        'approvers',
        'qr_position',
        'qr_code_path',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'approvers' => 'array',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Approval workflow methods
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'completed' && $this->current_step >= $this->total_steps;
    }

    public function getCurrentApproverId(): ?int
    {
        if (!$this->approvers || !is_array($this->approvers) || $this->current_step >= count($this->approvers)) {
            return null;
        }

        return $this->approvers[$this->current_step] ?? null;
    }

    public function getNextApproverId(): ?int
    {
        if (!$this->approvers || !is_array($this->approvers) || $this->current_step + 1 >= count($this->approvers)) {
            return null;
        }

        return $this->approvers[$this->current_step + 1] ?? null;
    }

    public function canBeApprovedBy(int $userId): bool
    {
        return $this->getCurrentApproverId() === $userId && $this->isPendingApproval();
    }

    public function moveToNextStep(): bool
    {
        if ($this->current_step < $this->total_steps) {
            $this->current_step++;
            $this->save();
            return true;
        }
        return false;
    }

    public function completeApproval(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function rejectApproval(): void
    {
        $this->status = 'rejected';
        $this->completed_at = now();
        $this->save();
    }
}
