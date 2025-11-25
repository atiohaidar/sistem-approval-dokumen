// API Types
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'user';
  created_at: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  // token may be omitted when backend sets httpOnly cookie instead of returning token
  token?: string;
}

export interface Document {
  id: number;
  title: string;
  description: string | null;
  status: 'draft' | 'pending_approval' | 'completed' | 'rejected' | 'cancelled';
  file_path: string;
  file_name: string;
  file_size: number;
  mime_type: string;
  template_id: number | null;
  created_by: number;
  creator?: User;
  template?: DocumentTemplate;
  approvers: number[][];
  current_level: number;
  level_progress: LevelProgress;
  qr_x: number;
  qr_y: number;
  qr_page: number;
  qr_code_path: string | null;
  submitted_at: string | null;
  completed_at: string | null;
  created_at: string;
  updated_at: string;
  approval_records?: ApprovalRecord[];
}

export interface LevelProgress {
  approved: number[];
  pending: number[];
  rejected: number[];
}

export interface ApprovalRecord {
  id: number;
  document_id: number;
  approver_id: number;
  approver?: User;
  action: 'approved' | 'rejected';
  notes: string | null;
  level: number;
  processed_at: string;
  created_at: string;
  updated_at: string;
}

export interface DocumentTemplate {
  id: number;
  name: string;
  description: string | null;
}

export interface CreateDocumentRequest {
  title: string;
  description: string | null;
  file: File;
  template_id: number | null;
  approvers: number[][];
  qr_x: number;
  qr_y: number;
  qr_page: number;
}

export interface ApprovalAction {
  action: 'approve' | 'reject';
  comments: string | null;
}

export interface DelegateApproval {
  delegate_to: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApprovalLevel {
  status: string;
  approvers: ApproverDetail[];
}

export interface ApproverDetail {
  id: number;
  user: User | null;
  status: string;
}

export interface DocumentDetailResponse {
  document: Document;
  approval_records: ApprovalRecord[];
}

export interface PublicDocumentInfo {
  document: Document;
  public_url: string;
  frontend_url: string;
  preview_url?: string;
  approval_progress: Record<number, LevelProgress>;
  approval_levels: Record<number, ApprovalLevel>;
  approval_records: ApprovalRecord[];
}
