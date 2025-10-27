<template>
  <div>
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
      <p class="text-gray-600 mt-2">Loading...</p>
    </div>

  <div v-else-if="doc">
      <div class="mb-8">
        <NuxtLink to="/documents" class="text-telkom-red hover:text-telkom-red-dark mb-4 inline-block">
          ← Kembali ke Dokumen
        </NuxtLink>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ doc.title }}</h1>
            <p class="text-gray-600 mt-2">{{ doc.description || 'Tidak ada deskripsi' }}</p>
          </div>
          <span :class="getStatusClass(doc.status)">
            {{ formatStatus(doc.status) }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Document Info -->
          <div class="card">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokumen</h2>
            <dl class="grid grid-cols-2 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Creator</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ doc.creator?.name || '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">File Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ doc.file_name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">File Size</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatFileSize(doc.file_size) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(doc.created_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Current Level</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ doc.current_level }} / {{ doc.approvers?.length || 0 }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ doc.completed_at ? formatDate(doc.completed_at) : '-' }}</dd>
              </div>
            </dl>

            <div class="mt-6">
              <button @click="handleDownload" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Dokumen
              </button>
            </div>
          </div>

          <!-- Approval Progress -->
          <div 
            class="animate-fade-in-up delay-200 transition-colors"
            :class="isDark ? 'bg-gray-800/80 border border-gray-700 text-gray-100' : 'bg-white/80'"
          >
            <GlassCard>
              <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h2 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Timeline Persetujuan</h2>
              </div>
              
              <div v-if="approvalTimelineData && approvalTimelineData.length > 0">
                <ApprovalTimeline :approval-levels="approvalTimelineData" />
              </div>
              <div v-else class="text-center py-8">
                <div 
                  class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                  :class="isDark ? 'bg-gray-800 text-gray-500' : 'bg-gray-100 text-gray-400'"
                >
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <p class="font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-600'">Belum ada proses persetujuan</p>
              </div>
            </GlassCard>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- QR Code Info -->
          <div class="card" v-if="doc.qr_code_path">
            <h2 class="text-lg font-bold text-gray-800 mb-4">QR Code</h2>
            <div class="text-center">
              <div class="bg-gray-100 p-4 rounded-lg inline-block">
                <p class="text-sm text-gray-600 mb-2">QR Code Position:</p>
                <p class="text-sm font-medium">X: {{ doc.qr_x }}, Y: {{ doc.qr_y }}</p>
                <p class="text-sm font-medium">Page: {{ doc.qr_page }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="card" v-if="canApprove">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Actions</h2>
            <div class="space-y-3">
              <button @click="showApprovalModal = true" class="w-full btn btn-primary">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve
              </button>
              <button @click="showRejectModal = true" class="w-full btn bg-red-600 text-white hover:bg-red-700">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Approve Dokumen</h3>
        <div class="mb-4">
          <label class="form-label">Comments (opsional)</label>
          <textarea
            v-model="approvalComments"
            class="form-input"
            rows="4"
            placeholder="Tambahkan komentar..."
          ></textarea>
        </div>
        <div class="flex gap-4">
          <button @click="handleApprove" class="btn btn-primary flex-1" :disabled="processing">
            {{ processing ? 'Processing...' : 'Approve' }}
          </button>
          <button @click="showApprovalModal = false" class="btn btn-secondary flex-1">
            Batal
          </button>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Reject Dokumen</h3>
        <div class="mb-4">
          <label class="form-label">Comments *</label>
          <textarea
            v-model="rejectComments"
            class="form-input"
            rows="4"
            placeholder="Alasan penolakan..."
            required
          ></textarea>
        </div>
        <div class="flex gap-4">
          <button @click="handleReject" class="btn bg-red-600 text-white hover:bg-red-700 flex-1" :disabled="processing">
            {{ processing ? 'Processing...' : 'Reject' }}
          </button>
          <button @click="showRejectModal = false" class="btn btn-secondary flex-1">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useDocuments } from '~/composables/useDocuments'
import { useApprovals } from '~/composables/useApprovals'
import { useUsers } from '~/composables/useUsers'
import type { Document, User } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { useDocumentQuery, downloadDocument } = useDocuments()
const { useProcessApprovalMutation } = useApprovals()
const { useUsersQuery } = useUsers()
const { isDark } = useTheme()

const documentId = computed(() => Number(route.params.id))

// Query for document
const { data: doc, isLoading: loading } = useDocumentQuery(documentId, {
  retry: 0,
})

// Query for users
const { data: users } = useUsersQuery()

// Mutation for approval
const processApprovalMutation = useProcessApprovalMutation()

const processing = computed(() => processApprovalMutation.isPending.value)
const showApprovalModal = ref(false)
const showRejectModal = ref(false)
const approvalComments = ref('')
const rejectComments = ref('')

const canApprove = computed(() => {
  if (!doc.value || !authStore.user) return false
  return doc.value.status === 'pending_approval' &&
    doc.value.level_progress?.pending?.includes(authStore.user.id)
})

// Transform document data into timeline format with approval records
const approvalTimelineData = computed(() => {
  if (!doc.value || !doc.value.approvers || !users.value) return []
  
  const levels = []
  const totalLevels = doc.value.approvers.length
  const approvalRecords = doc.value.approval_records || []
  
  for (let levelIndex = 0; levelIndex < totalLevels; levelIndex++) {
    const levelNumber = levelIndex + 1
    const approverIds = doc.value.approvers[levelIndex] || []
    
    // Determine level status
    let levelStatus = 'pending'
    let levelTimestamp = null
    
    if (levelNumber < doc.value.current_level) {
      levelStatus = 'completed'
    } else if (levelNumber === doc.value.current_level) {
      if (doc.value.status === 'completed') {
        levelStatus = 'completed'
        levelTimestamp = doc.value.completed_at
      } else if (doc.value.status === 'rejected') {
        levelStatus = 'rejected'
        levelTimestamp = doc.value.completed_at
      } else {
        levelStatus = 'in_progress'
      }
    }
    
    // Build approvers list for this level with approval records data
    const approvers = approverIds.map(approverId => {
      const user = users.value.find(u => u.id === approverId)
      let approverStatus = 'pending'
      let approverTimestamp = null
      let approverNotes = null
      
      // Find approval record for this approver at this level
      const approvalRecord = approvalRecords.find(record => 
        record.approver_id === approverId && record.level === levelNumber
      )
      
      if (approvalRecord) {
        approverStatus = approvalRecord.action === 'approved' ? 'approved' : 'rejected'
        approverTimestamp = approvalRecord.processed_at
        approverNotes = approvalRecord.notes
      } else {
        // Fallback to level progress data
        if (levelNumber < doc.value.current_level) {
          approverStatus = 'approved'
        } else if (levelNumber === doc.value.current_level && doc.value.level_progress) {
          if (doc.value.level_progress.approved?.includes(approverId)) {
            approverStatus = 'approved'
          } else if (doc.value.level_progress.rejected?.includes(approverId)) {
            approverStatus = 'rejected'
          }
        }
      }
      
      return {
        id: approverId,
        name: user?.name || 'Unknown User',
        status: approverStatus,
        timestamp: approverTimestamp,
        notes: approverNotes
      }
    })
    
    levels.push({
      status: levelStatus,
      approvers,
      timestamp: levelTimestamp
    })
  }
  
  return levels
})

// Watch for document load error and redirect
watch(() => doc.value, (newDoc) => {
  if (newDoc === undefined && !loading.value) {
    router.push('/documents')
  }
})

const handleDownload = async () => {
  try {
    const blob = await downloadDocument(documentId.value)
    const url = window.URL.createObjectURL(blob)
    const a = window.document.createElement('a')
    a.href = url
    a.download = doc.value?.file_name || 'document.pdf'
    window.document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    window.document.body.removeChild(a)
  } catch (error) {
    console.error('Error downloading document:', error)
    alert('Gagal mendownload dokumen')
  }
}

const handleApprove = async () => {
  try {
    await processApprovalMutation.mutateAsync({
      documentId: documentId.value,
      data: {
        action: 'approve',
        comments: approvalComments.value || null,
      },
    })
    showApprovalModal.value = false
    approvalComments.value = ''
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal approve dokumen')
  }
}

const handleReject = async () => {
  if (!rejectComments.value) {
    alert('Alasan penolakan harus diisi')
    return
  }
  
  try {
    await processApprovalMutation.mutateAsync({
      documentId: documentId.value,
      data: {
        action: 'reject',
        comments: rejectComments.value,
      },
    })
    showRejectModal.value = false
    rejectComments.value = ''
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal reject dokumen')
  }
}



const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'badge badge-pending',
    pending_approval: 'badge badge-pending',
    completed: 'badge badge-approved',
    rejected: 'badge badge-rejected',
  }
  return classes[status] || 'badge'
}

const formatStatus = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'Draft',
    pending_approval: 'Pending Approval',
    completed: 'Completed',
    rejected: 'Rejected',
  }
  return labels[status] || status
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

const formatFileSize = (bytes: number) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}
</script>
