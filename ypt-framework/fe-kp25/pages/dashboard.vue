<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <h2 class="fw-bold text-primary mb-1">Dashboard</h2>
        <p class="text-muted">Selamat datang, {{ authStore.user?.name }}</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
              <svg class="text-primary" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <h6 class="text-muted mb-1 small">Total Dokumen</h6>
              <h3 class="fw-bold mb-0">{{ stats.totalDocuments }}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
              <svg class="text-warning" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h6 class="text-muted mb-1 small">Menunggu Approval</h6>
              <h3 class="fw-bold mb-0">{{ stats.pendingApprovals }}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
              <svg class="text-success" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h6 class="text-muted mb-1 small">Dokumen Selesai</h6>
              <h3 class="fw-bold mb-0">{{ stats.completedDocuments }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-3">Aksi Cepat</h5>
            <div class="d-flex flex-wrap gap-2">
              <NuxtLink to="/documents/create" class="btn btn-primary">
                <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen Baru
              </NuxtLink>
              <NuxtLink to="/documents" class="btn btn-outline-primary">
                Lihat Semua Dokumen
              </NuxtLink>
              <NuxtLink to="/approvals" class="btn btn-outline-secondary">
                Approval Queue
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Documents -->
    <div class="row">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Dokumen Terbaru</h5>
            <NuxtLink to="/documents" class="btn btn-sm btn-link text-primary text-decoration-none">
              Lihat Semua →
            </NuxtLink>
          </div>
          <div class="card-body p-0">
            <div v-if="loadingDocuments" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="recentDocuments.length === 0" class="text-center py-5 text-muted">
              <svg class="mb-3" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <p>Belum ada dokumen</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="border-0">Judul</th>
                    <th class="border-0">Status</th>
                    <th class="border-0">Tanggal</th>
                    <th class="border-0">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="doc in recentDocuments" :key="doc.id">
                    <td>
                      <div class="fw-medium">{{ doc.title }}</div>
                      <div class="text-muted small">{{ doc.file_name }}</div>
                    </td>
                    <td>
                      <span class="badge" :class="getStatusBadge(doc.status)">
                        {{ getStatusLabel(doc.status) }}
                      </span>
                    </td>
                    <td class="text-muted small">{{ formatDate(doc.created_at) }}</td>
                    <td>
                      <NuxtLink :to="`/documents/${doc.id}`" class="btn btn-sm btn-outline-primary">
                        Detail
                      </NuxtLink>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
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

const authStore = useAuthStore()
const { getDocuments } = useDocuments()

const recentDocuments = ref<Document[]>([])
const loadingDocuments = ref(true)

const stats = reactive({
  totalDocuments: 0,
  pendingApprovals: 0,
  completedDocuments: 0,
})

const loadData = async () => {
  try {
    const result = await getDocuments({ page: 1 })
    recentDocuments.value = result.data.slice(0, 5)
    stats.totalDocuments = result.total

    // Calculate stats
    const allDocs = result.data
    stats.pendingApprovals = allDocs.filter(d => d.status === 'pending_approval').length
    stats.completedDocuments = allDocs.filter(d => d.status === 'completed').length
  } catch (error) {
    console.error('Failed to load documents:', error)
  } finally {
    loadingDocuments.value = false
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

onMounted(() => {
  loadData()
})
</script>
