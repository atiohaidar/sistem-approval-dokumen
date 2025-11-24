# Secure Document Access API

## Overview

This document describes the secure token-based document access system that protects documents from unauthorized access while providing flexible sharing capabilities.

## Key Features

- **Token-based Authentication**: Documents require valid access tokens
- **Configurable Expiration**: Tokens can expire after a specified time
- **Revocation & Rotation**: Tokens can be manually revoked or rotated to new tokens
- **Comprehensive Audit Logging**: All access attempts are logged with detailed information
- **Authorization Control**: Only authorized users can generate tokens
- **Brute-force Protection**: 256-bit secure random tokens

## Authorization

Users who can generate access tokens for a document:
1. **Document Creator**: The user who uploaded the document
2. **Approvers**: Users assigned to approve the document at any level
3. **Admins**: Users with admin role

## API Endpoints

### 1. Generate Access Token

Create a new access token for a document.

```http
POST /api/documents/{document_id}/access-tokens
Authorization: Bearer {sanctum_token}
Content-Type: application/json

{
  "expires_in_hours": 48,
  "purpose": "Share with external reviewer"
}
```

**Response (201 Created):**
```json
{
  "token_id": 123,
  "access_url": "http://localhost:3000/secure/abc123def456...",
  "expires_at": "2025-11-26T10:00:00.000000Z",
  "expires_in_hours": 48
}
```

**Parameters:**
- `expires_in_hours` (optional): Token expiration time in hours (1-8760). Default: 24 hours
- `purpose` (optional): Human-readable purpose for the token

### 2. List Active Tokens

Get all active (non-expired, non-revoked) tokens for a document.

```http
GET /api/documents/{document_id}/access-tokens
Authorization: Bearer {sanctum_token}
```

**Response (200 OK):**
```json
{
  "tokens": [
    {
      "id": 123,
      "generated_by": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "expires_at": "2025-11-26T10:00:00.000000Z",
      "access_count": 5,
      "last_accessed_at": "2025-11-24T15:30:00.000000Z",
      "metadata": {
        "purpose": "Share with external reviewer"
      },
      "created_at": "2025-11-24T10:00:00.000000Z"
    }
  ]
}
```

### 3. Revoke Token

Manually revoke an access token before it expires.

```http
POST /api/documents/{document_id}/access-tokens/{token_id}/revoke
Authorization: Bearer {sanctum_token}
Content-Type: application/json

{
  "reason": "Security concern - token potentially compromised"
}
```

**Response (200 OK):**
```json
{
  "message": "Token revoked successfully",
  "token_id": 123
}
```

**Parameters:**
- `reason` (optional): Reason for revocation

**Authorization:**
Only these users can revoke a token:
- The user who generated the token
- The document creator
- Admins

### 4. Rotate Token

Generate a new token and automatically revoke the old one.

```http
POST /api/documents/{document_id}/access-tokens/{token_id}/rotate
Authorization: Bearer {sanctum_token}
Content-Type: application/json

{
  "expires_in_hours": 48
}
```

**Response (200 OK):**
```json
{
  "token_id": 124,
  "access_url": "http://localhost:3000/secure/xyz789ghi012...",
  "expires_at": "2025-11-26T10:00:00.000000Z",
  "old_token_id": 123,
  "message": "Token rotated successfully"
}
```

**Parameters:**
- `expires_in_hours` (optional): New token expiration time. Default: 24 hours

### 5. Secure Document Access

Access document information using a token (no authentication required).

```http
GET /api/secure/documents/{token}
```

**Response (200 OK):**
```json
{
  "document": {
    "id": 1,
    "title": "Contract Agreement",
    "description": "Annual contract renewal",
    "status": "pending_approval",
    "file_name": "contract.pdf",
    "created_at": "2025-11-24T10:00:00.000000Z",
    "creator": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    }
  },
  "approval_progress": {
    "1": {
      "status": "in_progress",
      "approved": [2],
      "pending": [3],
      "rejected": []
    }
  },
  "token_expires_at": "2025-11-26T10:00:00.000000Z"
}
```

**Error Response (403 Forbidden):**
```json
{
  "message": "Invalid or expired access token"
}
```

### 6. Secure Document Preview

View document PDF inline using a token.

```http
GET /api/secure/documents/{token}/preview
```

**Response:** PDF file with `Content-Type: application/pdf` and `Content-Disposition: inline`

### 7. Secure Document Download

Download document PDF using a token.

```http
GET /api/secure/documents/{token}/download
```

**Response:** PDF file with `Content-Type: application/pdf` and `Content-Disposition: attachment`

### 8. Access Audit Logs

View access logs and statistics for a document (creator and admin only).

```http
GET /api/documents/{document_id}/access-logs?hours=24
Authorization: Bearer {sanctum_token}
```

**Response (200 OK):**
```json
{
  "stats": {
    "total_accesses": 15,
    "successful_accesses": 13,
    "failed_accesses": 2,
    "unique_users": 3,
    "unique_ips": 5,
    "actions": {
      "view": 8,
      "preview": 4,
      "download": 3
    }
  },
  "logs": {
    "data": [
      {
        "id": 1,
        "document_id": 1,
        "access_token_id": 123,
        "user_id": 2,
        "action": "view",
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0...",
        "success": true,
        "created_at": "2025-11-24T15:30:00.000000Z",
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      }
    ],
    "per_page": 50,
    "current_page": 1,
    "total": 15
  }
}
```

**Query Parameters:**
- `hours` (optional): Time window for logs in hours. Default: 24

## Token Security

### Token Generation

- Uses cryptographically secure `random_bytes(32)` for 256 bits of randomness
- Hashed with SHA-256 to produce 64-character hex string
- Total entropy: 2^256 possible tokens (practically impossible to brute-force)

### Token Format

- Length: 64 characters
- Character set: Hexadecimal (0-9, a-f)
- Example: `a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456`

### Token Validation

A token is valid only if:
1. It exists in the database
2. It has not been revoked (`revoked_at` is NULL)
3. It has not expired (`expires_at` is in the future)

## Audit Logging

All document access attempts are logged with:

- **Document ID**: Which document was accessed
- **Access Token ID**: Which token was used
- **User ID**: Who accessed (if authenticated)
- **Action**: `view`, `preview`, `download`, or `*_attempt` for failures
- **IP Address**: Client IP address
- **User Agent**: Client browser/app information
- **Referer**: HTTP referer header
- **Success**: Boolean indicating if access was granted
- **Failure Reason**: Reason for denial (if failed)
- **Timestamp**: When the access occurred

Failed access attempts (invalid/expired tokens) are also logged for security monitoring.

## Use Cases

### 1. QR Code on Document

When a document is created, a long-lived token (1 year) is automatically generated and embedded in the QR code. This allows:
- Scanning QR code to access document information
- Viewing approval status
- Previewing the document

### 2. Temporary Sharing

Generate a short-lived token (e.g., 24 hours) to share with external reviewers:
1. Creator generates token via API
2. Share the `access_url` with reviewer
3. Reviewer accesses document without authentication
4. Token automatically expires after 24 hours

### 3. Revocation Scenarios

Revoke tokens when:
- Security concern (token potentially compromised)
- Reviewer no longer needs access
- Document status changed (e.g., rejected)
- Regular security rotation

### 4. Token Rotation

Rotate tokens periodically for enhanced security:
- Generate new token
- Old token automatically revoked
- Update QR code with new token
- Maintain continuous access

## Migration from Public Endpoints

### Deprecated Endpoints

These endpoints still work but are deprecated:
- `GET /api/documents/{id}/public-info`
- `GET /api/documents/{id}/public-preview`

### Recommended Migration

1. **For New Documents**: Automatically uses secure tokens (no action needed)
2. **For Existing Documents**: Generate tokens for documents that need QR code access
3. **For QR Codes**: Regenerate QR codes with secure token URLs

Example migration script:
```bash
php artisan documents:regenerate-qrs --force
```

## Error Handling

### Common Errors

**401 Unauthorized** - Missing authentication token
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden** - Insufficient permissions or invalid token
```json
{
  "message": "Unauthorized to generate access token for this document"
}
```

**404 Not Found** - Document or token not found
```json
{
  "message": "Token not found"
}
```

**422 Unprocessable Entity** - Validation errors
```json
{
  "message": "The expires in hours field must be at least 1.",
  "errors": {
    "expires_in_hours": [
      "The expires in hours field must be at least 1."
    ]
  }
}
```

## Best Practices

1. **Token Expiration**: Use shortest possible expiration time that meets your needs
2. **Regular Rotation**: Rotate long-lived tokens periodically (e.g., quarterly)
3. **Immediate Revocation**: Revoke tokens immediately when no longer needed
4. **Monitor Logs**: Regularly review access logs for suspicious activity
5. **Limit Token Generation**: Only generate tokens when necessary
6. **Document Purpose**: Always include a clear purpose when generating tokens

## Security Considerations

1. **Token Storage**: Tokens are stored as-is in database (not hashed) to allow lookup
2. **HTTPS Only**: Always use HTTPS in production to prevent token interception
3. **Rate Limiting**: Consider implementing rate limiting on secure endpoints
4. **Token Length**: 64-character tokens provide sufficient security against brute-force
5. **Access Logging**: All access attempts are logged for audit purposes
6. **Authorization**: Only authorized users can generate/manage tokens

## Performance

- **Token Validation**: O(1) database lookup with indexed token column
- **Access Logging**: Asynchronous/background jobs recommended for high traffic
- **Token Cleanup**: Run periodic cleanup job to delete old expired tokens

Example cleanup command:
```bash
# Delete tokens expired more than 30 days ago
php artisan tinker
>>> app(\App\Services\DocumentAccessService::class)->cleanupExpiredTokens(30);
```
