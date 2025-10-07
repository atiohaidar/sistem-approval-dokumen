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
        'current_level',
        'level_progress',
        'qr_x',
        'qr_y',
        'qr_page',
        'qr_code_path',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'approvers' => 'array',
        'current_level' => 'integer',
        'level_progress' => 'array',
        'qr_x' => 'float',
        'qr_y' => 'float',
        'qr_page' => 'integer',
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

    // Approval workflow methods
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->status === 'completed' && $this->current_step >= $this->total_steps;
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

    // Multi-level approval methods
    public function getCurrentApproverIds(): array
    {
        if (!$this->approvers || !is_array($this->approvers) || $this->current_level > count($this->approvers)) {
            return [];
        }

        return $this->approvers[$this->current_level - 1] ?? [];
    }

    public function canBeApprovedBy(int $userId): bool
    {
        return in_array($userId, $this->getCurrentApproverIds()) && $this->isPendingApproval();
    }

    public function isLevelComplete(): bool
    {
        $currentApproverIds = $this->getCurrentApproverIds();
        if (empty($currentApproverIds)) {
            return false;
        }

        $progress = $this->getLevelProgress();
        $approvedCount = count($progress['approved'] ?? []);

        return $approvedCount === count($currentApproverIds);
    }

    public function moveToNextLevel(): bool
    {
        if ($this->current_level < count($this->approvers)) {
            $this->current_level++;
            $this->level_progress = $this->initializeLevelProgress();
            $this->save();
            return true;
        }
        return false;
    }

    public function approveByUser(int $userId): bool
    {
        if (!$this->canBeApprovedBy($userId)) {
            return false;
        }

        $progress = $this->getLevelProgress();
        if (!in_array($userId, $progress['approved'])) {
            $progress['approved'][] = $userId;
            $progress['pending'] = array_diff($progress['pending'], [$userId]);
            $this->level_progress = $progress;
            $this->save();

            // Check if level is complete
            if ($this->isLevelComplete()) {
                if (!$this->moveToNextLevel()) {
                    // All levels completed
                    $this->completeApproval();
                }
            }
        }

        return true;
    }

    public function rejectByUser(int $userId): bool
    {
        if (!$this->canBeApprovedBy($userId)) {
            return false;
        }

        $this->rejectApproval();
        return true;
    }

    public function getLevelProgress(): array
    {
        if (!$this->level_progress) {
            $this->level_progress = $this->initializeLevelProgress();
            $this->save();
        }

        return $this->level_progress;
    }

    private function initializeLevelProgress(): array
    {
        $currentApproverIds = $this->getCurrentApproverIds();
        return [
            'approved' => [],
            'pending' => $currentApproverIds
        ];
    }

    public function getTotalLevels(): int
    {
        return count($this->approvers ?? []);
    }

    public function getApprovalProgress(): array
    {
        $totalLevels = $this->getTotalLevels();
        $progress = [];

        for ($level = 1; $level <= $totalLevels; $level++) {
            if ($level < $this->current_level) {
                $progress[$level] = ['status' => 'completed', 'approved' => [], 'pending' => []];
            } elseif ($level === $this->current_level) {
                $levelProgress = $this->getLevelProgress();
                $progress[$level] = [
                    'status' => 'in_progress',
                    'approved' => $levelProgress['approved'],
                    'pending' => $levelProgress['pending']
                ];
            } else {
                $progress[$level] = ['status' => 'pending', 'approved' => [], 'pending' => $this->approvers[$level - 1]];
            }
        }

        return $progress;
    }
}
