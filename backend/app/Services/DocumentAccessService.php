<?php

namespace App\Services;

use App\Models\AccessAuditLog;
use App\Models\Document;
use App\Models\DocumentAccessToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentAccessService
{
    /**
     * Generate a secure access token for a document
     *
     * @param Document $document
     * @param User|null $generatedBy
     * @param int $expiresInHours Default 24 hours
     * @param array|null $metadata
     * @return DocumentAccessToken
     */
    public function generateToken(
        Document $document,
        ?User $generatedBy = null,
        int $expiresInHours = 24,
        ?array $metadata = null
    ): DocumentAccessToken {
        return DB::transaction(function () use ($document, $generatedBy, $expiresInHours, $metadata) {
            $token = DocumentAccessToken::generateSecureToken();
            
            return DocumentAccessToken::create([
                'document_id' => $document->id,
                'token' => $token,
                'generated_by' => $generatedBy?->id,
                'expires_at' => now()->addHours($expiresInHours),
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Validate a token and return the token model if valid
     *
     * @param string $token
     * @return DocumentAccessToken|null
     */
    public function validateToken(string $token): ?DocumentAccessToken
    {
        $accessToken = DocumentAccessToken::where('token', $token)
            ->with('document')
            ->first();

        if (!$accessToken) {
            return null;
        }

        if (!$accessToken->isValid()) {
            return null;
        }

        return $accessToken;
    }

    /**
     * Revoke a specific token
     *
     * @param DocumentAccessToken $token
     * @param string|null $reason
     * @return bool
     */
    public function revokeToken(DocumentAccessToken $token, ?string $reason = null): bool
    {
        $token->revoke($reason);
        return true;
    }

    /**
     * Revoke all tokens for a document
     *
     * @param Document $document
     * @param string|null $reason
     * @return int Number of tokens revoked
     */
    public function revokeAllTokensForDocument(Document $document, ?string $reason = null): int
    {
        $tokens = DocumentAccessToken::where('document_id', $document->id)
            ->valid()
            ->get();

        foreach ($tokens as $token) {
            $token->revoke($reason);
        }

        return $tokens->count();
    }

    /**
     * Rotate token - generate new token and revoke the old one
     *
     * @param DocumentAccessToken $oldToken
     * @param int $expiresInHours
     * @return DocumentAccessToken
     */
    public function rotateToken(DocumentAccessToken $oldToken, int $expiresInHours = 24): DocumentAccessToken
    {
        return DB::transaction(function () use ($oldToken, $expiresInHours) {
            // Generate new token
            $newToken = $this->generateToken(
                $oldToken->document,
                $oldToken->generatedBy,
                $expiresInHours,
                array_merge($oldToken->metadata ?? [], ['rotated_from' => $oldToken->id])
            );

            // Revoke old token
            $oldToken->revoke('Rotated to new token');

            return $newToken;
        });
    }

    /**
     * Log document access
     *
     * @param Document $document
     * @param string $action
     * @param DocumentAccessToken|null $accessToken
     * @param User|null $user
     * @param bool $success
     * @param string|null $failureReason
     * @param array|null $metadata
     * @return AccessAuditLog
     */
    public function logAccess(
        Document $document,
        string $action,
        ?DocumentAccessToken $accessToken = null,
        ?User $user = null,
        bool $success = true,
        ?string $failureReason = null,
        ?array $metadata = null
    ): AccessAuditLog {
        // Update token access count if token was used
        if ($accessToken && $success) {
            $accessToken->recordAccess();
        }

        return AccessAuditLog::logAccess(
            $document->id,
            $action,
            $success,
            $accessToken?->id,
            $user?->id,
            $failureReason,
            $metadata
        );
    }

    /**
     * Check if user is authorized to generate token for document
     *
     * @param Document $document
     * @param User $user
     * @return bool
     */
    public function canGenerateToken(Document $document, User $user): bool
    {
        // Creator can generate tokens
        if ($document->created_by === $user->id) {
            return true;
        }

        // Admins can generate tokens
        if ($user->isAdmin()) {
            return true;
        }

        // Approvers can generate tokens for documents they're assigned to
        if ($document->approvers && is_array($document->approvers)) {
            foreach ($document->approvers as $levelApprovers) {
                if (is_array($levelApprovers) && in_array($user->id, $levelApprovers)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clean up expired tokens (for scheduled task)
     *
     * @param int $daysOld Delete tokens expired more than X days ago
     * @return int Number of tokens deleted
     */
    public function cleanupExpiredTokens(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return DocumentAccessToken::where('expires_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Get access statistics for a document
     *
     * @param Document $document
     * @param int $hours
     * @return array
     */
    public function getAccessStats(Document $document, int $hours = 24): array
    {
        $logs = AccessAuditLog::where('document_id', $document->id)
            ->recent($hours)
            ->get();

        return [
            'total_accesses' => $logs->count(),
            'successful_accesses' => $logs->where('success', true)->count(),
            'failed_accesses' => $logs->where('success', false)->count(),
            'unique_users' => $logs->whereNotNull('user_id')->pluck('user_id')->unique()->count(),
            'unique_ips' => $logs->pluck('ip_address')->unique()->count(),
            'actions' => $logs->groupBy('action')->map->count()->toArray(),
        ];
    }

    /**
     * Get active tokens for a document
     *
     * @param Document $document
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTokens(Document $document)
    {
        return DocumentAccessToken::where('document_id', $document->id)
            ->valid()
            ->with('generatedBy:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
