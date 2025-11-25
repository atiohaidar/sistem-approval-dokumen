# Frontend - Sistem Approval Dokumen

Frontend application untuk Sistem Approval Dokumen Multi Tingkat dengan tema **Yayasan Pendidikan Telkom (YPT)**.

## 🚀 Tech Stack

- **Framework**: Nuxt.js 3 (Vue.js)
- **Styling**: Tailwind CSS v3 dengan color palette YPT/YPT
- **State Management**: Pinia
- **HTTP Client**: Axios
- **Type Safety**: TypeScript

## 🎨 Design System - YPT Theme

### Color Palette YPT
- Primary Red: `#EE3124` (Merah Telkom)
- Red Dark: `#C61E1E`
- Red Light: `#F25C50`
- Grey: `#6D6E71`
- Grey Dark: `#58595B`
- Grey Light: `#BCBEC0`
- Blue: `#0071BC`
- Blue Light: `#5CB3E5`
- Yellow Accent: `#FCD116` (untuk highlights)

### Design Features
- **Hero Section**: Background merah diagonal dengan logo besar dan teks bold
- **Navigation**: Top header dengan logo, search bar, dan social media icons
- **Navigation Bar**: Background merah dengan border bottom kuning pada active menu
- **Cards**: Shadow elevation dengan hover effects
- **Buttons**: Solid red dengan shadow, hover scale animation
- **Typography**: Bold headings dengan underline kuning accent

### Component Classes
- Buttons: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline`
- Cards: `.card`
- Forms: `.form-input`, `.form-label`
- Badges: `.badge`, `.badge-pending`, `.badge-approved`, `.badge-rejected`

## 📦 Installation

```bash
# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Update API base URL in .env
# NUXT_PUBLIC_API_BASE=http://localhost:8000/api
```

## 🏃 Development

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

## 📁 Project Structure

```
frontend/
├── app.vue                 # Root component
├── nuxt.config.ts         # Nuxt configuration
├── tailwind.config.js     # Tailwind configuration
├── assets/
│   └── css/
│       └── main.css       # Global styles & Tailwind
├── components/            # Reusable Vue components
├── composables/           # Composable functions
│   ├── useDocuments.ts   # Document API calls
│   ├── useApprovals.ts   # Approval API calls
│   └── useUsers.ts       # User API calls
├── layouts/
│   └── default.vue       # Main layout with sidebar
├── middleware/
│   └── auth.ts           # Authentication middleware
├── pages/                # Route pages
│   ├── index.vue        # Landing page
│   ├── login.vue        # Login page
│   ├── register.vue     # Register page
│   ├── dashboard.vue    # Dashboard
│   ├── documents/       # Document management
│   ├── approvals/       # Approval pages
│   ├── users/          # User management (admin)
│   └── public/         # Public document info (QR)
├── plugins/
│   └── api.ts          # Axios plugin with interceptors
├── stores/
│   └── auth.ts         # Auth store (Pinia)
└── types/
    └── api.ts          # TypeScript types
```

## 🔐 Features Implemented

### Authentication
- [x] Login page with Telkom theme
- [x] Register page
- [x] JWT token authentication
- [x] Auto-redirect on auth status
- [x] Logout functionality

### Dashboard
- [x] Statistics cards
- [x] Recent documents list
- [x] Quick actions
- [x] Pending approvals count

### Document Management
- [x] List all documents with filters (status, search, creator)
- [x] Create document with PDF upload
- [x] Multi-level approver configuration
- [x] QR code position selector
- [x] Document detail view
- [x] Download document
- [x] Approval progress visualization
- [x] Delete draft documents

### Approval System
- [x] List pending approvals
- [x] View document details
- [x] Approve/Reject with comments
- [x] Quick approve functionality
- [x] Level-based approval tracking

### User Management (Admin Only)
- [x] List all users
- [x] Create new user
- [x] Edit user (name, email, role)
- [x] Delete user
- [x] Role management (admin/user)

### Public Features
- [x] Public document info page (for QR code scan)
- [x] Approval progress visualization
- [x] Verification badge

### Additional Features
- [x] Responsive design for mobile
- [x] Loading states
- [x] Error handling
- [x] Pagination
- [x] Date formatting (Indonesian locale)
- [x] File size formatting

## 🌐 API Integration

All API endpoints are integrated through composables:

- `useDocuments()` - Document CRUD operations
- `useApprovals()` - Approval processing
- `useUsers()` - User management

Base API URL is configured in `.env` file.

## 🎯 Pages & Routes

| Route | Page | Auth Required | Admin Only |
|-------|------|---------------|------------|
| `/` | Landing page | No | No |
| `/login` | Login | No | No |
| `/register` | Register | No | No |
| `/dashboard` | Dashboard | Yes | No |
| `/documents` | Document list | Yes | No |
| `/documents/create` | Create document | Yes | No |
| `/documents/[id]` | Document detail | Yes | No |
| `/approvals` | Pending approvals | Yes | No |
| `/users` | User management | Yes | Yes |
| `/public/[id]` | Public document info | No | No |

## 🔒 Security

- JWT token stored in cookies (7 days expiry)
- Automatic token refresh on API calls
- Auto-logout on 401 response
- Role-based access control
- Protected routes with middleware

## 🎨 YPT Branding

The application uses YPT's official color scheme:
- Primary brand color (Red #EE3124) for CTAs and important elements
- Grey (#6D6E71) for secondary elements
- Blue (#0071BC) for informational elements

All UI components follow Telkom's design language with clean, professional styling.

## 📝 Environment Variables

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api
```

## 🤝 Contributing

This frontend is designed to work with the Laravel backend API. Ensure backend is running before starting frontend development.

## 📄 License

Property of YPT
