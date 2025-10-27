# Sistem Approval Dokumen Multi Tingkat

Platform internal Yayasan Pendidikan Telkom (YPT) untuk mengelola approval dokumen secara digital dengan alur multi-level. Repositori ini berisi dua aplikasi utama:

- **backend/** – API Laravel + Sanctum untuk autentikasi, manajemen dokumen, workflow approval, dan generasi QR Code.
- **frontend/** – Nuxt 3 + Tailwind yang menyediakan dashboard, manajemen dokumen, alur approval, serta halaman publik berbasis QR.

> Logo dan tema warna telah disesuaikan dengan brand Telkom Indonesia (`frontend/public/logo.png`).

## Daftar Isi

1. [Ringkasan Fitur](#ringkasan-fitur)
2. [Highlight Pembaruan](#highlight-pembaruan)
3. [Struktur Proyek](#struktur-proyek)
4. [Prasyarat](#prasyarat)
5. [Langkah Setup](#langkah-setup)
6. [Variabel Lingkungan](#variabel-lingkungan)
7. [Menjalankan Aplikasi](#menjalankan-aplikasi)
8. [Pengujian](#pengujian)
9. [Operasional & Maintenance](#operasional--maintenance)
10. [Permukaan API](#permukaan-api)
11. [Troubleshooting](#troubleshooting)
12. [Kontribusi](#kontribusi)

## Ringkasan Fitur

### Backend (Laravel 11)
- Autentikasi berbasis Laravel Sanctum (cookie + bearer) lengkap dengan middleware role admin/user.
- CRUD dokumen PDF dengan validasi ukuran/mime dan penyimpanan di `storage/app/public/documents`.
- Workflow approval multi-level: level paralel (dalam satu array) berjalan bersamaan, level berbeda sequential.
- Delegasi approval di level yang sama serta pencatatan aksi (approve/reject) pada tabel `document_approvals`.
- Generasi QR Code yang menunjuk ke halaman publik frontend, lengkap dengan re-generasi saat status berubah.
- Watermark otomatis “BELUM APPROVE” + penyematan QR ketika file belum selesai disetujui.
- Endpoint publik `/api/documents/{id}/public-info` + `/public-preview` untuk kebutuhan QR scan.

### Frontend (Nuxt 3)
- UI bertema Telkom Indonesia dengan sidebar responsif dan dark-mode toggle.
- Autentikasi (login, register, logout) menggunakan Pinia + Axios interceptors (withCredentials, CSRF fetch).
- Dashboard statistik: pending approvals, dokumen milik pengguna, riwayat dokumen terbaru.
- Manajemen dokumen dengan filter, pencarian, pagination, hapus draft, serta detail dokumen.
- **Interactive QR Positioning**: drag & drop overlay pada preview PDF untuk menentukan koordinat QR (tersimpan sebagai `qr_x`, `qr_y`, `qr_page`).
- Halaman approval untuk reviewer (approve/reject dengan catatan) dan halaman publik `public/[id]` dengan preview PDF inline.
- Admin-only user management (list, buat, ubah role, hapus).

## Highlight Pembaruan

| Area | Pembaruan utama |
|------|-----------------|
| Frontend | Tema Telkom lengkap (logo, warna, typography), perbaikan hamburger sidebar, integrasi logo pada login/register. |
| Frontend | Perbaikan CSRF + cookie parsing sehingga login stabil (Axios withCredentials, prefetch `/sanctum/csrf-cookie`). |
| Frontend | Interactive QR positioning dengan preview PDF, public document preview iframe, robust download handling. |
| Backend  | Endpoint public preview (`/public-preview`), command `documents:regenerate-qrs`, logging approval, sanitasi delegasi approval. |
| Backend  | PDF watermark + QR embedding via `PDFWatermarkService`, perbaikan multi-level approval (progress tracking & delegation). |

Seluruh perubahan terdokumentasi secara kronologis pada `docs/Prompt-Tracking.md`.

## Struktur Proyek

```
.
├── backend/                 # API Laravel + Sanctum
│   ├── app/
│   │   ├── Console/Commands/RegenerateQRCodes.php
│   │   ├── Http/Controllers/Api/{Auth,Document,Approval,User}Controller.php
│   │   ├── Models/{Document,DocumentApproval,DocumentTemplate,User}.php
│   │   └── Services/{ApprovalService,QRCodeService,PDFWatermarkService}.php
│   ├── database/
│   │   ├── migrations/     # Struktur tabel users, documents, document_approvals, dll.
│   │   └── seeders/
│   └── routes/api.php
├── frontend/                # Nuxt 3 dengan Tailwind + Pinia
│   ├── app.vue, nuxt.config.ts, tailwind.config.js
│   ├── assets/css/         # main.css + utilitas glassmorphism & animasi
│   ├── components/         # GradientButton, GlassCard, dsb.
│   ├── composables/        # useDocuments, useApprovals, useUsers
│   ├── layouts/default.vue # Sidebar + header logo Telkom
│   ├── middleware/auth.ts  # Proteksi route
│   ├── pages/              # Dashboard, Documents, Approvals, Users, Public info
│   └── stores/auth.ts
└── docs/                   # Prompt tracking & dokumentasi tambahan
```

## Prasyarat

- PHP 8.2+ & Composer 2
- Node.js 20.19+ & npm 10+
- SQLite (default) atau database lain yang kompatibel
- Git, Gitbash/PowerShell (Windows)

## Langkah Setup

### 1. Clone repository

```bash
git clone https://github.com/atiohaidar/sistem-approval-dokumen.git
cd sistem-approval-dokumen
```

### 2. Backend (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate

# Default menggunakan SQLite.
touch database/database.sqlite

# Jalankan migrasi & seeder dasar.
php artisan migrate --seed

# Buat symbolic link storage -> public
php artisan storage:link
```

Setel variabel penting pada `.env` backend (lihat bagian [Variabel Lingkungan](#variabel-lingkungan)).

Untuk menjalankan API:

```bash
php artisan serve
```

### 3. Frontend (Nuxt 3)

```bash
cd ../frontend
npm install
cp .env.example .env

# Pastikan base URL API mengarah ke backend yang berjalan
echo "NUXT_PUBLIC_API_BASE=http://localhost:8000/api" >> .env
```

Jalankan pengembangan:

```bash
npm run dev
```

## Variabel Lingkungan

### Backend `.env`

| Variabel | Deskripsi |
|----------|-----------|
| `APP_URL` | URL backend (contoh `http://localhost:8000`). |
| `FRONTEND_URL` | Digunakan QR code (`http://localhost:3000`). |
| `SANCTUM_STATEFUL_DOMAINS` | Daftar domain frontend yang dianggap stateful (contoh `localhost:3000,127.0.0.1:3000`). |
| `SESSION_DOMAIN` | Domain cookie sesi (`localhost`). |
| `SESSION_DRIVER` | Gunakan `database` (sudah default) agar CSRF kompatibel. |
| `FILESYSTEM_DISK` | Pastikan `public` agar file tersimpan di `storage/app/public`. |

### Frontend `.env`

| Variabel | Deskripsi |
|----------|-----------|
| `NUXT_PUBLIC_API_BASE` | Base URL API backend, contoh `http://localhost:8000/api`. |

## Menjalankan Aplikasi

1. Pastikan backend dan frontend berjalan (perintah di atas) pada dua terminal terpisah.
2. Akses frontend melalui `http://localhost:3000`.
3. Login menggunakan akun seeded (`admin@example.com` / `password`) atau registrasi baru.

### Alur Singkat

1. **Upload dokumen** via halaman *Documents › Upload Dokumen Baru*.
2. Pilih approver per level (array 2 dimensi) dan atur posisi QR melalui preview interaktif.
3. Approver menerima dokumen pada halaman *Approvals* untuk approve/reject/delegate.
4. QR Code mengarah ke halaman publik `public/:id` yang menampilkan status + preview PDF.

## Pengujian

Backend memiliki cakupan test fitur `php artisan test` (±70 test).

```bash
cd backend
php artisan test
```

Pastikan Anda menjalankan ulang migrasi dengan database testing (`php artisan test --parallel` otomatis memakai database sementara SQLite).

Frontend saat ini belum memiliki unit test. Gunakan manual testing atau tambahkan Playwright/Vitest sesuai kebutuhan.

## Operasional & Maintenance

| Task | Perintah / Keterangan |
|------|-----------------------|
| Regenerasi QR untuk semua dokumen | `php artisan documents:regenerate-qrs --force` |
| Regenerasi QR tertentu | `php artisan documents:regenerate-qrs --ids=1,5,10` |
| Membersihkan file sementara watermark | Hapus isi `storage/app/public/temp` secara berkala (cron atau manual). |
| Backup dokumen | Backup direktori `storage/app/public/documents` & `qr-codes`. |
| Log aplikasi | `storage/logs/laravel.log` (server), pastikan rotasi log. |
| Update dependencies backend | `composer update` (lakukan setelah backup). |
| Update dependencies frontend | `npm outdated`, `npm update`, rebuild dengan `npm run build`. |
| Storage symlink check | Jalankan ulang `php artisan storage:link` setelah deploy baru. |

Pastikan server production menjalankan scheduler & queue jika kelak menambahkan job asynchronous (`php artisan schedule:work`, `queue:work`). Saat ini job berat (watermark/QR) berjalan sinkron.

### Monitoring

- Aktifkan log level `info`/`notice` untuk audit approval (lihat `ApprovalController`).
- Gunakan tools seperti Sentry/Logtail untuk menangkap error produksi.
- Tambah pengecekan health endpoint (misal route `GET /api/health`) bila diperlukan.

## Permukaan API

Endpoint utama tersedia di `routes/api.php`. Ringkasan:

- **Auth**: `POST /auth/login`, `POST /auth/register`, `POST /auth/logout`, `GET /auth/user`.
- **Documents**: `GET /documents`, `POST /documents`, `GET /documents/{id}`, `DELETE /documents/{id}`, `GET /documents/{id}/download`.
- **Approvals**: `GET /approvals/pending`, `POST /approvals/documents/{id}/process`, `POST /approvals/documents/{id}/delegate`.
- **Public**: `GET /documents/{id}/public-info`, `GET /documents/{id}/public-preview`.
- **Users (admin)**: resource `users` + `POST /users/{id}/change-role`.

Setiap endpoint menggunakan middleware `auth:sanctum` kecuali public info/preview dan login/register.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `CSRF token mismatch` saat login | Pastikan frontend memanggil `/sanctum/csrf-cookie`, `.env` backend berisi `SANCTUM_STATEFUL_DOMAINS` & `SESSION_DOMAIN`, serta Axios `withCredentials=true`. |
| Dokumen tidak bisa diunduh / `document.createElement` error | Sudah diperbaiki dengan penamaan variabel `doc` (bukan `document`). Pastikan frontend diperbarui. |
| Sidebar tidak menutup di desktop | Diperbaiki melalui toggle state + class translation. Hard refresh jika masih cache lama. |
| PDF preview tidak muncul | Pastikan `php artisan storage:link` telah dijalankan dan file tersedia pada disk `public`. Cek console log untuk CORS/404. |

## Kontribusi

1. Fork & buat branch fitur.
2. Pastikan `php artisan test` dan pengecekan lint berjalan.
3. Dokumentasikan perubahan pada bagian *Highlight Pembaruan* atau tambahkan catatan di `docs/Prompt-Tracking.md`.
4. Ajukan Pull Request dengan deskripsi jelas.

---

**Status**: Pengembangan aktif (frontend & backend siap digunakan secara internal). Lihat `docs/Prompt-Tracking.md` untuk kronologi perubahan terbaru.
# Sistem Approval Dokumen Multi Tingkat

Aplikasi web untuk manajemen approval dokumen digital dengan tanda tangan digital dan tracking yang komprehensif, khusus untuk kebutuhan YPT (Yayasan Pendidikan Teknologi).

## 📋 Deskripsi Proyek

Sistem ini memungkinkan organisasi untuk mengelola proses approval dokumen secara digital dengan fitur:
- **Multi-level approval** yang dapat dikonfigurasi
- **Tanda tangan digital** dengan verifikasi hash
- **Tracking lengkap** untuk mencegah kecurangan
- **Fleksibilitas approval flow** (parallel/sequential)
- **Template dokumen** dengan approval bawaan

## 🎯 Fitur Utama

### 📄 Manajemen Dokumen
- Upload dokumen dalam format PDF
- **Interactive QR Code Positioning** - Drag & drop QR code placement dengan visual preview
- Template dokumen dengan approval flow bawaan (dapat diedit)
- Tracking versi dan riwayat dokumen
- Support dokumen dari berbagai sumber (internal/eksternal)
- Real-time PDF preview untuk QR code positioning

### ⚡ Sistem Approval
- **Multi-level approval** yang dapat dikonfigurasi per dokumen
- **Parallel processing** - beberapa approver dapat bekerja bersamaan
- **Sequential processing** - approval berurutan jika diperlukan
- **Emergency skip** - melewati approval dengan alasan yang valid
- **Replacement approver** - delegasi jika approver utama tidak tersedia
- **Revision system** - kembalikan dokumen untuk perbaikan

### 🔐 Digital Signature & Security
- Tanda tangan digital menggunakan hash verification dengan **QR Code**
- Formula: `Signature = Hash(DocumentContent + UserID + Timestamp + SecretKey)`
- **QR Code Content**: `DocumentID|SignatureHash|Timestamp|UserID|VerificationURL`
- QR Code dapat di-scan untuk verifikasi keaslian signature
- Audit trail lengkap dengan IP address dan browser info
- Anti-fraud protection dengan tracking keaslian signature
- Immutable signature history

### 👥 Role Management
- **Admin** - Setup approval flow dan konfigurasi sistem
- **Document Creator** - Upload dan submit dokumen untuk approval
- **Approver** - Memberikan tanda tangan digital pada dokumen
- **Observer** - View-only access untuk monitoring
- **Manager** - Dapat melakukan emergency skip approval
- **Multi-role support** - Satu user dapat memiliki beberapa role

## 🚀 Teknologi Stack

### Backend
- **Framework**: Laravel (PHP)
- **Database**: SQLite (lightweight, portable)
- **Authentication**: Laravel Sanctum + Role-based Access Control
- **File Storage**: Local storage untuk PDF files
- **API**: RESTful API dengan Laravel Resource

### Frontend
- **Framework**: Nuxt.js (Vue.js)
- **UI Library**: Nuxt UI / Tailwind CSS
- **State Management**: Pinia
- **SSR**: Server-Side Rendering untuk performance

### Security & Digital Signature
- Hash-based digital signatures dengan **QR Code verification**
- QR Code berisi: Document ID + Signature Hash + Timestamp + User Info
- Encrypted data transmission (HTTPS)
- Secure file storage dengan access control
- Comprehensive audit logging

### Security
- Hash-based digital signatures
- Encrypted data transmission
- Secure file storage
- Audit logging

## 📊 Dashboard & Fitur UI

### 🏠 Dashboard Utama
- **Pending Approvals** - Dokumen menunggu approval dari user
- **Document Status Tracking** - Status real-time semua dokumen
- **Statistics** - Metrics approval per bulan, bottleneck analysis
- **Quick Actions** - Shortcut untuk tugas umum

### 🔔 Notification System
- **In-app notifications** - Alert untuk dokumen baru dan update
- **Reminder system** - Peringatan jika approval terlalu lama
- **Skip alerts** - Notifikasi jika ada approval yang di-skip
- **Dashboard badges** - Counter untuk pending items

### 📈 Reporting & Analytics
- Approval history dan audit trail
- Performance metrics per approver
- Document processing time analysis
- Bottleneck identification
- Export reports (PDF/Excel)

## 🔄 Workflow Approval

### Skenario Approval Flow
1. **Document Upload** → Creator upload dokumen atau gunakan template
2. **Flow Configuration** → Sistem menerapkan approval flow (default/custom)
3. **Parallel/Sequential Processing** → Approver bekerja sesuai konfigurasi
4. **Digital Signature** → Setiap approval menghasilkan signature hash
5. **Completion/Revision** → Dokumen selesai atau dikembalikan untuk revisi

### Emergency Procedures
- **Skip Approval**: Semua role dapat skip dengan alasan valid
- **Replacement**: Approver dapat diganti jika tidak tersedia
- **Revision Request**: Dokumen dapat dikembalikan ke creator untuk perbaikan

## 🛡️ Security Features

### Digital Signature Verification dengan QR Code
```
Signature Components:
- Document content hash (SHA-256)
- User ID dan timestamp  
- Secret key encryption
- IP address logging
- Browser fingerprint

QR Code Content:
- Document ID + Signature Hash
- Timestamp + User Info
- Verification URL + Expiry
- JSON format untuk easy parsing
```

### Audit Trail
- Siapa melakukan apa dan kapan
- IP address dan device information
- Approval/rejection reasons
- Skip approval justification
- Document modification history

## 🏗️ Database Schema (SQLite)

### 👤 Users Table
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    position VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 🎭 Roles & Permissions
```sql
CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL, -- admin, creator, approver, observer, manager
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### 📄 Documents & Templates
```sql
CREATE TABLE document_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    is_active BOOLEAN DEFAULT 1,
    default_approval_flow_id INTEGER,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INTEGER,
    mime_type VARCHAR(100) DEFAULT 'application/pdf',
    template_id INTEGER NULL,
    status ENUM('draft', 'pending', 'in_progress', 'completed', 'rejected', 'cancelled') DEFAULT 'draft',
    created_by INTEGER NOT NULL,
    submitted_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    current_step INTEGER DEFAULT 0,
    total_steps INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (template_id) REFERENCES document_templates(id)
);
```

### ⚡ Approval Flows
```sql
CREATE TABLE approval_flows (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_template_default BOOLEAN DEFAULT 0,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE approval_steps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    flow_id INTEGER NOT NULL,
    step_order INTEGER NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    step_type ENUM('sequential', 'parallel') DEFAULT 'sequential',
    is_required BOOLEAN DEFAULT 1,
    can_skip BOOLEAN DEFAULT 0,
    minimum_approvers INTEGER DEFAULT 1, -- untuk parallel type
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flow_id) REFERENCES approval_flows(id) ON DELETE CASCADE
);

CREATE TABLE step_approvers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    step_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    is_backup BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (step_id) REFERENCES approval_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 📝 Document Processing
```sql
CREATE TABLE document_approvals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_id INTEGER NOT NULL,
    flow_id INTEGER NOT NULL,
    current_step_id INTEGER,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (flow_id) REFERENCES approval_flows(id),
    FOREIGN KEY (current_step_id) REFERENCES approval_steps(id)
);

CREATE TABLE approval_actions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_approval_id INTEGER NOT NULL,
    step_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    action ENUM('approve', 'reject', 'skip', 'delegate') NOT NULL,
    comments TEXT,
    skip_reason TEXT NULL,
    delegated_to INTEGER NULL,
    signature_hash VARCHAR(255),
    qr_code_data TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_approval_id) REFERENCES document_approvals(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES approval_steps(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (delegated_to) REFERENCES users(id)
);
```

### 🔐 Digital Signatures & QR Codes
```sql
CREATE TABLE digital_signatures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    approval_action_id INTEGER NOT NULL,
    signature_hash VARCHAR(255) NOT NULL,
    qr_code_path VARCHAR(500),
    qr_code_data TEXT NOT NULL,
    verification_url VARCHAR(500),
    is_valid BOOLEAN DEFAULT 1,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approval_action_id) REFERENCES approval_actions(id)
);

CREATE TABLE signature_verifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    signature_id INTEGER NOT NULL,
    verified_by INTEGER,
    verification_method ENUM('qr_scan', 'manual', 'api') NOT NULL,
    verification_result BOOLEAN NOT NULL,
    verification_details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (signature_id) REFERENCES digital_signatures(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);
```

### 🔔 Notifications & Audit
```sql
CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type ENUM('approval_request', 'document_approved', 'document_rejected', 'document_completed', 'reminder', 'skip_alert') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    document_id INTEGER,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (document_id) REFERENCES documents(id)
);

CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INTEGER,
    old_values TEXT, -- JSON
    new_values TEXT, -- JSON
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 📊 Analytics & Reports
```sql
CREATE TABLE document_analytics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_id INTEGER NOT NULL,
    total_processing_time INTEGER, -- dalam menit
    average_step_time INTEGER,
    total_approvers INTEGER,
    skip_count INTEGER DEFAULT 0,
    revision_count INTEGER DEFAULT 0,
    bottleneck_step_id INTEGER,
    completed_at TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (bottleneck_step_id) REFERENCES approval_steps(id)
);
```

## 🔄 QR Code Structure

### QR Code Content Format
```json
{
    "document_id": "123",
    "signature_hash": "sha256_hash_string",
    "timestamp": "2024-01-15T10:30:00Z",
    "user_id": "456",
    "user_name": "John Doe",
    "verification_url": "https://domain.com/verify/abc123",
    "expires_at": "2025-01-15T10:30:00Z"
}
```

### Verification Process
1. **Scan QR Code** → Extract data
2. **API Call** → `GET /api/verify/{signature_hash}`
3. **Hash Validation** → Compare with stored signature
4. **Response** → Valid/Invalid + Document details

### API Endpoints (Laravel)
```
Authentication:
- POST /api/auth/login
- POST /api/auth/logout  
- GET /api/auth/user

Documents:
- GET /api/documents
- POST /api/documents
- GET /api/documents/{id}
- PUT /api/documents/{id}
- DELETE /api/documents/{id}
- POST /api/documents/{id}/submit

Approvals:
- GET /api/approvals/pending
- POST /api/approvals/{id}/approve
- POST /api/approvals/{id}/reject
- POST /api/approvals/{id}/skip
- POST /api/approvals/{id}/delegate

Templates:
- GET /api/templates
- POST /api/templates
- GET /api/templates/{id}

Signatures & QR:
- POST /api/signatures/generate
- GET /api/signatures/verify/{hash}
- GET /api/qr/{signature_id}

Analytics:
- GET /api/analytics/dashboard
- GET /api/analytics/performance
- GET /api/analytics/bottlenecks

Admin:
- GET /api/admin/users
- POST /api/admin/flows
- GET /api/admin/audit-logs
```

```bash
# Clone repository
git clone https://github.com/atiohaidar/sistem-approval-dokumen.git

# Install dependencies
cd sistem-approval-dokumen
[package-manager-commands]

# Setup database
[database-setup-commands]

# Run application
[run-commands]
```

## 🤝 Kontribusi

Project ini dikembangkan untuk kebutuhan YPT (Yayasan Pendidikan Teknologi). 

## 📞 Support

Untuk pertanyaan atau support, silakan buat issue di repository ini.

---

**Status**: 📋 Planning Phase  
**Last Updated**: [Current Date]  
**Version**: 0.1.0-planning