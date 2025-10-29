# API Documentation

Complete API documentation for the Sistem Approval Dokumen backend.

## Base URL

```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

## Authentication

This API uses Laravel Sanctum for authentication with HTTP-only cookies.

### Authentication Flow

1. Login returns an HTTP-only cookie with the access token
2. Subsequent requests automatically include the cookie
3. Frontend must enable `withCredentials` in Axios/Fetch

### Headers

All authenticated requests must include:
```
Accept: application/json
Content-Type: application/json (for JSON requests)
Content-Type: multipart/form-data (for file uploads)
```

## Rate Limiting

- **Authentication endpoints**: 10 requests per minute
- **Public endpoints**: 60 requests per minute
- **Authenticated endpoints**: 60 requests per minute per user

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60
```

## Error Responses

Standard error response format:
```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity (Validation Error)
- `429` - Too Many Requests
- `500` - Internal Server Error
- `503` - Service Unavailable

---

## Endpoints

For complete endpoint documentation, see the expanded API documentation.

### Quick Reference

#### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/logout` - User logout
- `GET /auth/user` - Get current user

#### Documents
- `GET /documents` - List documents
- `POST /documents` - Create document
- `GET /documents/{id}` - Get document details
- `PUT /documents/{id}` - Update document
- `DELETE /documents/{id}` - Delete document
- `GET /documents/{id}/download` - Download document

#### Public Documents
- `GET /documents/{id}/public-info` - Get public document info
- `GET /documents/{id}/public-preview` - Preview document

#### Approvals
- `GET /approvals/pending` - Get pending approvals
- `POST /approvals/documents/{id}/process` - Approve/reject document
- `POST /approvals/documents/{id}/delegate` - Delegate approval

#### Users (Admin Only)
- `GET /users` - List users
- `POST /users` - Create user
- `GET /users/{id}` - Get user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/{id}/change-role` - Change user role

#### Health Check
- `GET /health` - Basic health check
- `GET /health/detailed` - Detailed system info (admin only)

---

**Last Updated**: 2025-10-29
**API Version**: 1.0.0
