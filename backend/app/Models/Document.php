<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'qr_code_path',
        'qr_code_data',
        'qr_position',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'qr_code_data' => 'array',
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

    /**
     * Get all approval processes for this document.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Get the current approval process.
     */
    public function currentApproval()
    {
        return $this->hasOne(DocumentApproval::class)->latest();
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

    /**
     * Check if QR code exists.
     */
    public function hasQrCode(): bool
    {
        return !empty($this->qr_code_path) && !empty($this->qr_code_data);
    }

    /**
     * Get QR code position label.
     */
    public function getQrPositionLabel(): string
    {
        $positions = [
            'top-left' => 'Kiri Atas',
            'top-right' => 'Kanan Atas',
            'bottom-left' => 'Kiri Bawah',
            'bottom-right' => 'Kanan Bawah',
            'center' => 'Tengah',
        ];

        return $positions[$this->qr_position] ?? 'Tidak Diketahui';
    }

    /**
     * Get approval status for QR display.
     */
    public function getApprovalStatusForQr(): array
    {
        $currentApproval = $this->currentApproval;

        if (!$currentApproval) {
            return [
                'status' => 'Belum Diajukan',
                'current_step' => 0,
                'total_steps' => 0,
                'is_completed' => false,
            ];
        }

        return [
            'status' => $currentApproval->status,
            'current_step' => $currentApproval->current_step_order,
            'total_steps' => $currentApproval->approvalFlow->steps->count(),
            'is_completed' => $currentApproval->isApproved() || $currentApproval->isRejected(),
        ];
    }
}
