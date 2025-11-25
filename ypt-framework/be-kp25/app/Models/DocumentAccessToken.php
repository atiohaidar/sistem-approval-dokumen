<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessToken extends Model
{
    protected $fillable = [
        'document_id',
        'token',
        'generated_by',
        'expires_at',
        'revoked_at',
        'revoked_reason',
        'access_count',
        'last_accessed_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'metadata' => 'array',
        'access_count' => 'integer',
    ];

    protected $hidden = [
        'token', // Don't expose raw token in API responses by default
    ];

    // Relationships
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Generate a secure random token
     * Uses cryptographically secure random bytes and SHA-256 hashing
     */
    public static function generateSecureToken(): string
    {
        // Generate 32 bytes (256 bits) of cryptographically secure randomness
        // Then hash with SHA-256 to produce a 64-character hex string
        return hash('sha256', random_bytes(32));
    }

    /**
     * Check if token is valid (not expired and not revoked)
     */
    public function isValid(): bool
    {
        // Check if revoked
        if ($this->revoked_at !== null) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if token is revoked
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Revoke the token
     */
    public function revoke(?string $reason = null): void
    {
        $this->revoked_at = now();
        $this->revoked_reason = $reason;
        $this->save();
    }

    /**
     * Increment access count and update last accessed time
     */
    public function recordAccess(): void
    {
        $this->access_count++;
        $this->last_accessed_at = now();
        $this->save();
    }

    /**
     * Scope to get only valid tokens
     */
    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->whereNull('revoked_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope to get revoked tokens
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }
}
