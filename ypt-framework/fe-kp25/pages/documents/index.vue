<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-primary mb-1">Dokumen</h2>
        <p class="text-muted mb-0">Kelola semua dokumen Anda</p>
      </div>
      <NuxtLink to="/documents/create" class="btn btn-primary">
        <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Upload Dokumen
      </NuxtLink>
    </div>

    <!-- Filter and Search -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Cari dokumen..."
            />
          </div>
          <div class="col-md-3">
            <select v-model="statusFilter" class="form-select">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="pending_approval">Menunggu Approval</option>
              <option value="completed">Selesai</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents List -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="documents.length === 0" class="text-center py-5 text-muted">
          <svg class="mb-3" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p>Belum ada dokumen</p>
          <NuxtLink to="/documents/create" class="btn btn-primary mt-2">
            Upload Dokumen Pertama
          </NuxtLink>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0">Judul</th>
                <th class="border-0">Status</th>
                <th class="border-0">Level</th>
                <th class="border-0">Tanggal</th>
                <th class="border-0">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in documents" :key="doc.id">
                <td>
                  <div class="fw-medium">{{ doc.title }}</div>
                  <div class="text-muted small">{{ doc.file_name }}</div>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadge(doc.status)">
                    {{ getStatusLabel(doc.status) }}
                  </span>
                </td>
                <td>
                  <span class="text-muted">
                    Level {{ doc.current_level }} / {{ doc.approvers?.length || 0 }}
                  </span>
                </td>
                <td class="text-muted small">{{ formatDate(doc.created_at) }}</td>
                <td>
                  <div class="btn-group">
                    <NuxtLink :to="`/documents/${doc.id}`" class="btn btn-sm btn-outline-primary">
                      Detail
                    </NuxtLink>
                    <button
                      v-if="doc.status === 'draft'"
                      class="btn btn-sm btn-outline-danger"
                      @click="confirmDelete(doc)"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="goToPage(currentPage - 1)">Previous</button>
          </li>
          <li 
            v-for="page in totalPages" 
            :key="page" 
            class="page-item" 
            :class="{ active: page === currentPage }"
          >
            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="goToPage(currentPage + 1)">Next</button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { getDocuments, deleteDocument } = useDocuments()

const documents = ref<Document[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const loadDocuments = async () => {
  isLoading.value = true
  try {
    const result = await getDocuments({
      page: currentPage.value,
      status: statusFilter.value || undefined,
      search: searchQuery.value || undefined,
    })
    documents.value = result.data
    totalPages.value = result.last_page
  } catch (error) {
    console.error('Failed to load documents:', error)
  } finally {
    isLoading.value = false
  }
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadDocuments()
  }
}

const confirmDelete = async (doc: Document) => {
  if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${doc.title}"?`)) {
    try {
      await deleteDocument(doc.id)
      loadDocuments()
    } catch (error) {
      console.error('Failed to delete document:', error)
      alert('Gagal menghapus dokumen')
    }
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
    month: 'short',
    day: 'numeric',
  })
}

// Watch for filter changes
watch([searchQuery, statusFilter], () => {
  currentPage.value = 1
  loadDocuments()
})

onMounted(() => {
  loadDocuments()
})
</script>
