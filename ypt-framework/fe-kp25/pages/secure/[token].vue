<template>
  <div class="min-vh-100 bg-light py-5">
    <div class="container" style="max-width: 800px;">
      <div class="text-center mb-4">
        <img src="/logo.png" alt="YPT Logo" style="width: 60px; height: 60px;" class="mb-3" />
        <h4 class="fw-bold text-primary">Sistem Approval Dokumen</h4>
        <p class="text-muted">Informasi Dokumen</p>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-3">Memuat dokumen...</p>
      </div>

      <div v-else-if="error" class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <svg class="text-danger mb-3" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <h5 class="fw-semibold text-danger">{{ error }}</h5>
          <p class="text-muted">Silakan periksa kembali tautan atau hubungi administrator.</p>
        </div>
      </div>

      <div v-else-if="document">
        <!-- Document Info -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h4 class="fw-bold mb-2">{{ document.title }}</h4>
                <p class="text-muted mb-0">{{ document.description || 'Tidak ada deskripsi' }}</p>
              </div>
              <span class="badge" :class="getStatusBadge(document.status)">
                {{ getStatusLabel(document.status) }}
              </span>
            </div>

            <hr class="my-4">

            <div class="row g-3">
              <div class="col-md-3">
                <div class="text-muted small">Creator</div>
                <div class="fw-medium">{{ document.creator?.name || '-' }}</div>
              </div>
              <div class="col-md-3">
                <div class="text-muted small">File</div>
                <div class="fw-medium">{{ document.file_name }}</div>
              </div>
              <div class="col-md-3">
                <div class="text-muted small">Tanggal Dibuat</div>
                <div class="fw-medium">{{ formatDate(document.created_at) }}</div>
              </div>
              <div class="col-md-3">
                <div class="text-muted small">Status</div>
                <div class="fw-medium">{{ getStatusLabel(document.status) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview -->
        <div v-if="previewUrl" class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Preview Dokumen</h5>
            <a :href="previewUrl" target="_blank" class="btn btn-sm btn-outline-primary">
              Buka di Tab Baru
            </a>
          </div>
          <div class="card-body p-0">
            <div style="height: 500px; overflow: hidden;">
              <iframe
                :src="previewUrl + '#view=FitH'"
                style="width: 100%; height: 100%; border: none;"
                title="Preview Dokumen"
              ></iframe>
            </div>
          </div>
        </div>

        <!-- Approval Timeline -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-transparent border-0">
            <h5 class="mb-0 fw-semibold">Timeline Persetujuan</h5>
          </div>
          <div class="card-body">
            <ApprovalTimeline 
              v-if="approvalTimelineData && approvalTimelineData.length > 0"
              :approval-levels="approvalTimelineData" 
            />
            <div v-else class="text-center py-4 text-muted">
              Belum ada proses persetujuan
            </div>
          </div>
        </div>

        <!-- Verification Badge -->
        <div class="card border-0 shadow-sm" :class="document.status === 'completed' ? 'border-success' : 'border-warning'" style="border-width: 2px !important;">
          <div class="card-body d-flex align-items-start">
            <svg class="text-success flex-shrink-0 me-3 mt-1" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
              <h6 class="fw-bold text-success mb-1">Dokumen Terverifikasi</h6>
              <p class="text-muted mb-0 small">
                Dokumen ini telah diverifikasi dan terdaftar dalam sistem approval YPT.
                Informasi di atas menunjukkan status terkini dari proses approval dokumen.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Document, ApprovalLevel } from '~/types/api'

definePageMeta({
  layout: false,
})

const route = useRoute()
const config = useRuntimeConfig()

const token = computed(() => route.params.token as string)
const document = ref<Document | null>(null)
const approvalLevels = ref<Record<number, ApprovalLevel>>({})
const approvalRecords = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const previewUrl = ref<string | null>(null)

const apiBase = config.public.apiBase

const approvalTimelineData = computed(() => {
  if (!document.value || !approvalLevels.value) return []
  
  const levels = []
  const levelNumbers = Object.keys(approvalLevels.value).map(Number).sort()
  
  for (const levelNumber of levelNumbers) {
    const level = approvalLevels.value[levelNumber]
    if (!level) continue
    
    let levelStatus: 'completed' | 'in_progress' | 'rejected' | 'pending' = 'pending'
    if (level.status === 'completed') {
      levelStatus = 'completed'
    } else if (level.status === 'in_progress') {
      levelStatus = 'in_progress'
    } else if (level.status === 'rejected') {
      levelStatus = 'rejected'
    }
    
    const approvers = level.approvers.map(approver => {
      let approverStatus: 'approved' | 'rejected' | 'pending' = 'pending'
      if (approver.status === 'approved') {
        approverStatus = 'approved'
      } else if (approver.status === 'rejected') {
        approverStatus = 'rejected'
      }
      
      const record = approvalRecords.value.find(
        r => r.approver_id === approver.id && r.level === levelNumber
      )
      
      return {
        id: approver.id,
        name: approver.user?.name || 'Unknown User',
        status: approverStatus,
        timestamp: record?.processed_at,
        notes: record?.notes,
      }
    })
    
    levels.push({ status: levelStatus, approvers })
  }
  
  return levels
})

const loadDocumentInfo = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch(`${apiBase}/secure/documents/${token.value}`)
    if (!response.ok) {
      const data = await response.json().catch(() => ({}))
      throw new Error(data.message || 'Token akses tidak valid atau telah kedaluwarsa')
    }
    
    const info = await response.json()
    document.value = info.document
    approvalLevels.value = info.approval_levels || {}
    approvalRecords.value = info.approval_records || []
    
    // Check if preview is available
    const previewCandidate = `${apiBase}/secure/documents/${token.value}/preview`
    try {
      const headResp = await fetch(previewCandidate, { method: 'HEAD' })
      if (headResp && headResp.ok) {
        previewUrl.value = previewCandidate
      }
    } catch (err) {
      previewUrl.value = null
    }
  } catch (err: any) {
    console.error('Error loading document info:', err)
    error.value = err.message || 'Gagal memuat informasi dokumen'
    document.value = null
  } finally {
    loading.value = false
  }
}

const getStatusBadge = (status: string) => {
  const map: Record<string, string> = {
    draft: 'bg-secondary',
    pending_approval: 'bg-warning text-dark',
    completed: 'bg-success',
    rejected: 'bg-danger',
  }
  return map[status] || 'bg-secondary'
}

const getStatusLabel = (status: string) => {
  const map: Record<string, string> = {
    draft: 'Draft',
    pending_approval: 'Menunggu Approval',
    completed: 'Selesai',
    rejected: 'Ditolak',
  }
  return map[status] || status
}

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  loadDocumentInfo()
})
</script>
