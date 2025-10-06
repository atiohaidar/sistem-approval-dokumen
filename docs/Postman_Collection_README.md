# Sistem Approval Dokumen - Postman Collection

This Postman collection contains all API endpoints for the Document Approval System based on the test files.

## Setup Instructions

1. **Import the Collection**
   - Open Postman
   - Click "Import" button
   - Select "File" tab
   - Choose `docs/Postman_Collection.json`

2. **Configure Environment Variables**
   - Create a new environment in Postman
   - Add the following variables:
     - `base_url`: `http://localhost:8000/api` (adjust for your setup)
     - `auth_token`: (leave empty, will be set after login)
     - `document_id`: (leave empty, will be set after creating document)
     - `approval_id`: (leave empty, will be set after submitting for approval)

## API Endpoints Overview

### Authentication
- **POST** `/auth/register` - Register new user
- **POST** `/auth/login` - Login user
- **GET** `/auth/user` - Get current user info
- **POST** `/auth/logout` - Logout user

### Document Management
- **GET** `/documents` - List documents (with pagination and filters)
- **POST** `/documents` - Create new document (with file upload)
- **GET** `/documents/{id}` - View specific document
- **PUT** `/documents/{id}` - Update document (draft status only)
- **DELETE** `/documents/{id}` - Delete document (draft status only)

### Approval System
- **POST** `/documents/{id}/approval/submit` - Submit document for approval
- **POST** `/approvals/{id}/approve` - Approve document
- **POST** `/approvals/{id}/reject` - Reject document
- **GET** `/documents/{id}/approval/history` - Get approval history
- **GET** `/approvals/pending` - Get pending approvals for current user

## Usage Workflow

1. **Register/Login** to get authentication token
2. **Create Document** with PDF file upload
3. **Submit Document** for approval using an approval flow
4. **Approve/Reject** documents (as assigned approver)
5. **View History** and pending approvals

## Authentication

All endpoints except registration and login require Bearer token authentication:
```
Authorization: Bearer {your_token_here}
```

## File Upload

Document creation requires multipart/form-data with:
- `title`: Document title
- `description`: Document description
- `file`: PDF file (max 10MB)
- `qr_position`: QR code position (top-left, top-right, bottom-left, bottom-right, center)

## Response Examples

Each request includes example responses showing the expected JSON structure and status codes.

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `201`: Created
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error

## Notes

- Document updates and deletions only work for documents in "draft" status
- Only document owners can update/delete their documents
- Approval actions require specific approver permissions
- QR codes are automatically generated on document creation
- All endpoints support proper error messages and validation