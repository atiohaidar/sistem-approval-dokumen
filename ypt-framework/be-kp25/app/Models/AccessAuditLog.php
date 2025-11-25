<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessAuditLog extends Model
{
    const UPDATED_AT = null; // Only use created_at

    protected $fillable = [
        'document_id',
        'access_token_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'referer',
        'success',
        'failure_reason',
        'metadata',
    ];

    protected $casts = [
        'success' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(DocumentAccessToken::class, 'access_token_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a document access attempt
     */
    public static function logAccess(
        int $documentId,
        string $action,
        bool $success = true,
        ?int $accessTokenId = null,
        ?int $userId = null,
        ?string $failureReason = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'document_id' => $documentId,
            'access_token_id' => $accessTokenId,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'success' => $success,
            'failure_reason' => $failureReason,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope to get successful accesses
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope to get failed accesses
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to get accesses for a specific action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get recent accesses
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
