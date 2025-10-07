# Sistem Approval Dokumen API - Postman Collection

## 📋 Deskrips#### 📄 Documents Management
- **GET** `/documents` - List dokumen dengan filter
- **POST** `/documents` - Upload dokumen baru (2 variants: array atau string approvers)
- **GET** `/documents/{id}` - Get detail dokumen
- **PUT** `/documents/{id}` - Update dokumen (draft only)
- **DELETE** `/documents/{id}` - Delete dokumen (draft only)
- **GET** `/documents/{id}/download` - Download dengan watermark otomatisction Postman untuk testin### 📝 Request Examples

### Login
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

### Create Document (Form Data)
```
title: Kontrak Kerja Sama PT ABC
description: Dokumen kontrak kerja sama
file: [Upload PDF file]
approvers: [2,3,4] atau "[2,3,4]" (bisa array atau string)
qr_position: top-right
```

**Format Approvers:**
- Array: `[2,3,4]`
- JSON String: `"[2,3,4]"`
- Comma-separated: `"2,3,4"` (kurang direkomendasikan)nt (Form Data):**
```
title: Kontrak Kerja Sama PT ABC
description: Dokumen kontrak kerja sama
file: [Upload PDF file]
approvers: [2,3,4] atau "[2,3,4]" (bisa array atau string)
qr_position: top-right
```m Approval Dokumen dengan fitur QR Code dan PDF Watermarking.

## 🚀 Setup Environment

### 1. Import Collection
1. Buka Postman
2. Klik **Import**
3. Pilih file `Sistem-Approval-Dokumen-API.postman_collection.json`

### 2. Setup Environment Variables
Buat environment baru di Postman dengan variables berikut:

| Variable | Value | Description |
|----------|-------|-------------|
| `base_url` | `http://localhost:8000/api` | Base URL Laravel API |
| `auth_token` | `` | Token akan terisi otomatis setelah login |

### 3. Setup Laravel Backend
```bash
cd backend
composer install
php artisan migrate
php artisan db:seed  # Untuk membuat user admin
php artisan serve    # Jalankan di port 8000
```

## 📚 API Endpoints Overview

### 🔐 Authentication
- **POST** `/auth/login` - Login user
- **POST** `/auth/register` - Register user baru
- **POST** `/auth/logout` - Logout (butuh auth token)
- **GET** `/auth/user` - Get user data (butuh auth token)

### 📄 Documents Management
- **GET** `/documents` - List dokumen dengan filter
- **POST** `/documents` - Upload dokumen baru
- **GET** `/documents/{id}` - Get detail dokumen
- **PUT** `/documents/{id}` - Update dokumen (draft only)
- **DELETE** `/documents/{id}` - Delete dokumen (draft only)
- **GET** `/documents/{id}/download` - Download dengan watermark otomatis

### ✅ Approval System
- **GET** `/approvals/pending` - List dokumen pending approval
- **POST** `/approvals/documents/{id}/process` - Approve/reject dokumen

### 👥 User Management (Admin Only)
- **GET** `/users` - List semua users
- **POST** `/users` - Create user baru
- **GET** `/users/{id}` - Get user detail
- **PUT** `/users/{id}` - Update user
- **DELETE** `/users/{id}` - Delete user
- **POST** `/users/{id}/change-role` - Change user role

## 🔄 Workflow Testing Lengkap

### 1. Setup Users
```bash
# Login sebagai admin (dari seeder)
Email: admin@example.com
Password: password123
```

### 2. Create Additional Users
Gunakan endpoint **Create User** untuk membuat user approver:
- User 1: approver1@example.com
- User 2: approver2@example.com
- User 3: approver3@example.com

### 3. Upload Document
1. Login sebagai admin atau user biasa
2. Gunakan endpoint **Create Document** atau **Create Document (String Approvers)**
3. Upload file PDF
4. Set approvers: `[2,3,4]` (array) atau `"[2,3,4]"` (string)
5. Set QR position: `top-right`

### 4. Test Approval Workflow
1. Login sebagai approver pertama (user ID 2)
2. Check **Get Pending Approvals** - akan muncul dokumen
3. **Process Approval** dengan action `approve`
4. Login sebagai approver kedua (user ID 3)
5. Ulangi proses approval
6. Login sebagai approver ketiga (user ID 4)
7. Approve dokumen terakhir

### 5. Test Download dengan Watermark
1. Selama approval process, download dokumen akan memiliki watermark "BELUM APPROVE" (teks abu-abu terang horizontal di tengah halaman)
2. Setelah semua approved, download akan memberikan file asli tanpa watermark

## 📝 Request Examples

### Login
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

### Create Document (Form Data)
```
title: Kontrak Kerja Sama PT ABC
description: Dokumen kontrak kerja sama
file: [Upload PDF file]
approvers: [2,3,4]
qr_position: top-right
```

### Process Approval
```json
{
  "action": "approve",
  "comments": "Dokumen telah direview dan disetujui"
}
```

## 🔑 Authentication Notes

- Semua endpoint kecuali login/register butuh `Authorization: Bearer {{auth_token}}`
- Token didapat dari response login/register
- Set variable `{{auth_token}}` di environment untuk auto-populate

## 📊 Response Codes

- `200` - Success
- `201` - Created
- `403` - Forbidden (tidak punya akses)
- `422` - Validation error
- `500` - Server error

## 🐛 Troubleshooting

1. **Token expired**: Login ulang untuk dapat token baru
2. **File upload gagal**: Pastikan file PDF dan size < 10MB
3. **Approval gagal**: Pastikan user adalah current approver
4. **Download gagal**: Pastikan user punya akses ke dokumen

## 📋 Test Scenarios

- ✅ Upload dokumen dengan multiple approvers (array format)
- ✅ Upload dokumen dengan multiple approvers (string format)
- ✅ Sequential approval workflow
- ✅ Download dengan watermark (teks abu-abu terang horizontal) sebelum approved
- ✅ Download tanpa watermark setelah approved
- ✅ Authorization checks (creator, approver, admin)
- ✅ File validation (PDF only, size limit)
- ✅ Status tracking (draft → pending → in_progress → completed)

---

**Happy Testing! 🚀**