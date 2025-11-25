<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <h2 class="fw-bold text-primary mb-1">Approval Queue</h2>
      <p class="text-muted mb-0">Dokumen yang menunggu persetujuan Anda</p>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="pendingDocuments.length === 0" class="text-center py-5 text-muted">
          <svg class="mb-3" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="mb-0">Tidak ada dokumen yang menunggu approval</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0">Dokumen</th>
                <th class="border-0">Creator</th>
                <th class="border-0">Level</th>
                <th class="border-0">Tanggal</th>
                <th class="border-0">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in pendingDocuments" :key="doc.id">
                <td>
                  <div class="fw-medium">{{ doc.title }}</div>
                  <div class="text-muted small">{{ doc.file_name }}</div>
                </td>
                <td>{{ doc.creator?.name || '-' }}</td>
                <td>
                  <span class="badge bg-secondary">
                    Level {{ doc.current_level }}
                  </span>
                </td>
                <td class="text-muted small">{{ formatDate(doc.created_at) }}</td>
                <td>
                  <div class="btn-group">
                    <NuxtLink :to="`/documents/${doc.id}`" class="btn btn-sm btn-outline-primary">
                      Detail
                    </NuxtLink>
                    <button
                      class="btn btn-sm btn-success"
                      @click="openApprovalModal(doc, 'approve')"
                    >
                      Approve
                    </button>
                    <button
                      class="btn btn-sm btn-danger"
                      @click="openApprovalModal(doc, 'reject')"
                    >
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ modalAction === 'approve' ? 'Approve' : 'Reject' }} Dokumen
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <strong>{{ selectedDocument?.title }}</strong>
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan (opsional)</label>
              <textarea
                v-model="comment"
                class="form-control"
                rows="3"
                placeholder="Tambahkan catatan..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button
              type="button"
              class="btn"
              :class="modalAction === 'approve' ? 'btn-success' : 'btn-danger'"
              :disabled="processing"
              @click="submitApproval"
            >
              <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
              {{ modalAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { getPendingApprovals, processApproval } = useApprovals()

const pendingDocuments = ref<Document[]>([])
const isLoading = ref(true)

const showModal = ref(false)
const selectedDocument = ref<Document | null>(null)
const modalAction = ref<'approve' | 'reject'>('approve')
const comment = ref('')
const processing = ref(false)

const loadPendingApprovals = async () => {
  isLoading.value = true
  try {
    pendingDocuments.value = await getPendingApprovals()
  } catch (error) {
    console.error('Failed to load pending approvals:', error)
  } finally {
    isLoading.value = false
  }
}

const openApprovalModal = (doc: Document, action: 'approve' | 'reject') => {
  selectedDocument.value = doc
  modalAction.value = action
  comment.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedDocument.value = null
}

const submitApproval = async () => {
  if (!selectedDocument.value) return

  processing.value = true
  try {
    await processApproval(selectedDocument.value.id, {
      action: modalAction.value,
      comments: comment.value || null,
    })
    closeModal()
    loadPendingApprovals()
  } catch (error) {
    console.error('Failed to process approval:', error)
    alert('Gagal memproses approval')
  } finally {
    processing.value = false
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  loadPendingApprovals()
})
</script>
