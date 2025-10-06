<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_action_id',
        'signature_data',
        'signature_hash',
        'certificate_info',
        'verification_status', // 'pending', 'verified', 'failed'
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'certificate_info' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the approval action this signature belongs to.
     */
    public function approvalAction(): BelongsTo
    {
        return $this->belongsTo(ApprovalAction::class);
    }

    /**
     * Check if signature is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if signature verification failed.
     */
    public function isFailed(): bool
    {
        return $this->verification_status === 'failed';
    }

    /**
     * Check if signature is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Verify signature hash.
     */
    public function verifyHash(string $data): bool
    {
        return hash('sha256', $data) === $this->signature_hash;
    }
}
