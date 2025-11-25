<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <NuxtLink to="/documents" class="text-primary text-decoration-none">
        ← Kembali ke Dokumen
      </NuxtLink>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="!document" class="text-center py-5 text-muted">
      <p>Dokumen tidak ditemukan</p>
    </div>

    <div v-else>
      <!-- Document Header -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h3 class="fw-bold mb-2">{{ document.title }}</h3>
              <p class="text-muted mb-0">{{ document.description || 'Tidak ada deskripsi' }}</p>
            </div>
            <span class="badge" :class="getStatusBadge(document.status)">
              {{ getStatusLabel(document.status) }}
            </span>
          </div>

          <hr class="my-4">

          <div class="row g-4">
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
              <div class="text-muted small">Level Saat Ini</div>
              <div class="fw-medium">{{ document.current_level }} / {{ document.approvers?.length || 0 }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="fw-semibold mb-3">Aksi</h5>
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary" @click="handleDownload">
              <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download
            </button>
            <button
              v-if="canApprove"
              class="btn btn-success"
              @click="handleApprove"
            >
              Approve
            </button>
            <button
              v-if="canApprove"
              class="btn btn-danger"
              @click="handleReject"
            >
              Reject
            </button>
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

      <!-- Approval History -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
          <h5 class="mb-0 fw-semibold">Riwayat Approval</h5>
        </div>
        <div class="card-body p-0">
          <div v-if="document.approval_records?.length" class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="border-0">Approver</th>
                  <th class="border-0">Level</th>
                  <th class="border-0">Aksi</th>
                  <th class="border-0">Catatan</th>
                  <th class="border-0">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in document.approval_records" :key="record.id">
                  <td>{{ record.approver?.name || '-' }}</td>
                  <td>Level {{ record.level }}</td>
                  <td>
                    <span class="badge" :class="record.action === 'approved' ? 'bg-success' : 'bg-danger'">
                      {{ record.action === 'approved' ? 'Disetujui' : 'Ditolak' }}
                    </span>
                  </td>
                  <td>{{ record.notes || '-' }}</td>
                  <td class="text-muted small">{{ formatDate(record.processed_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-4 text-muted">
            Belum ada riwayat approval
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ approvalAction === 'approve' ? 'Approve Dokumen' : 'Reject Dokumen' }}</h5>
            <button type="button" class="btn-close" @click="closeApprovalModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Catatan (opsional)</label>
              <textarea
                v-model="approvalComment"
                class="form-control"
                rows="3"
                placeholder="Tambahkan catatan..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeApprovalModal">Batal</button>
            <button
              type="button"
              class="btn"
              :class="approvalAction === 'approve' ? 'btn-success' : 'btn-danger'"
              :disabled="processingApproval"
              @click="submitApproval"
            >
              <span v-if="processingApproval" class="spinner-border spinner-border-sm me-2"></span>
              {{ approvalAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Document, ApprovalLevel } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const { getDocument, downloadDocument } = useDocuments()
const { processApproval } = useApprovals()
const authStore = useAuthStore()

const documentId = computed(() => Number(route.params.id))
const document = ref<Document | null>(null)
const isLoading = ref(true)

// Approval modal
const showApprovalModal = ref(false)
const approvalAction = ref<'approve' | 'reject'>('approve')
const approvalComment = ref('')
const processingApproval = ref(false)

const canApprove = computed(() => {
  if (!document.value || !authStore.user) return false
  if (document.value.status !== 'pending_approval') return false
  
  const currentLevel = document.value.current_level
  const approvers = document.value.approvers?.[currentLevel - 1] || []
  return approvers.includes(authStore.user.id)
})

const approvalTimelineData = computed(() => {
  if (!document.value) return []
  
  const levels = []
  const totalLevels = document.value.approvers?.length || 0
  const currentLevel = document.value.current_level
  const levelProgress = document.value.level_progress || { approved: [], pending: [], rejected: [] }
  
  for (let i = 0; i < totalLevels; i++) {
    const levelApprovers = document.value.approvers?.[i] || []
    const levelNum = i + 1
    
    let status: 'completed' | 'in_progress' | 'rejected' | 'pending' = 'pending'
    if (levelNum < currentLevel) {
      status = 'completed'
    } else if (levelNum === currentLevel) {
      if (document.value.status === 'rejected') {
        status = 'rejected'
      } else if (document.value.status === 'completed') {
        status = 'completed'
      } else {
        status = 'in_progress'
      }
    }
    
    const approvers = levelApprovers.map((approverId: number) => {
      const record = document.value?.approval_records?.find(
        r => r.approver_id === approverId && r.level === levelNum
      )
      
      let approverStatus: 'approved' | 'rejected' | 'pending' = 'pending'
      if (record) {
        approverStatus = record.action
      } else if (levelProgress.approved?.includes(approverId)) {
        approverStatus = 'approved'
      } else if (levelProgress.rejected?.includes(approverId)) {
        approverStatus = 'rejected'
      }
      
      return {
        id: approverId,
        name: record?.approver?.name || `User #${approverId}`,
        status: approverStatus,
        timestamp: record?.processed_at,
        notes: record?.notes,
      }
    })
    
    levels.push({ status, approvers })
  }
  
  return levels
})

const loadDocument = async () => {
  isLoading.value = true
  try {
    document.value = await getDocument(documentId.value)
  } catch (error) {
    console.error('Failed to load document:', error)
    document.value = null
  } finally {
    isLoading.value = false
  }
}

const handleDownload = async () => {
  try {
    const blob = await downloadDocument(documentId.value)
    const url = URL.createObjectURL(blob)
    const a = window.document.createElement('a')
    a.href = url
    a.download = document.value?.file_name || 'document.pdf'
    window.document.body.appendChild(a)
    a.click()
    window.document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Failed to download:', error)
    alert('Gagal mendownload dokumen')
  }
}

const handleApprove = () => {
  approvalAction.value = 'approve'
  approvalComment.value = ''
  showApprovalModal.value = true
}

const handleReject = () => {
  approvalAction.value = 'reject'
  approvalComment.value = ''
  showApprovalModal.value = true
}

const closeApprovalModal = () => {
  showApprovalModal.value = false
}

const submitApproval = async () => {
  processingApproval.value = true
  try {
    await processApproval(documentId.value, {
      action: approvalAction.value,
      comments: approvalComment.value || null,
    })
    closeApprovalModal()
    loadDocument()
  } catch (error) {
    console.error('Failed to process approval:', error)
    alert('Gagal memproses approval')
  } finally {
    processingApproval.value = false
  }
}

const getStatusBadge = (status: string) => {
  const map: Record<string, string> = {
    draft: 'bg-secondary',
    pending_approval: 'bg-warning text-dark',
    completed: 'bg-success',
    rejected: 'bg-danger',
    cancelled: 'bg-secondary',
  }
  return map[status] || 'bg-secondary'
}

const getStatusLabel = (status: string) => {
  const map: Record<string, string> = {
    draft: 'Draft',
    pending_approval: 'Pending',
    completed: 'Selesai',
    rejected: 'Ditolak',
    cancelled: 'Dibatalkan',
  }
  return map[status] || status
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  loadDocument()
})
</script>
