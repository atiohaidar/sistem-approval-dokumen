<template>
  <div class="min-vh-100 bg-light py-5">
    <div class="container" style="max-width: 800px;">
      <div class="text-center mb-4">
        <img src="/logo.png" alt="YPT Logo" style="width: 60px; height: 60px;" class="mb-3" />
        <h4 class="fw-bold text-primary">Sistem Approval Dokumen</h4>
        <p class="text-muted">Informasi Dokumen Publik</p>
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
          <p class="text-muted">Dokumen tidak tersedia untuk akses publik.</p>
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
              <div class="col-md-4">
                <div class="text-muted small">Creator</div>
                <div class="fw-medium">{{ document.creator?.name || '-' }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">Tanggal Dibuat</div>
                <div class="fw-medium">{{ formatDate(document.created_at) }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">Status</div>
                <div class="fw-medium">{{ getStatusLabel(document.status) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Verification Badge -->
        <div class="card border-0 shadow-sm border-success" style="border-width: 2px !important;">
          <div class="card-body d-flex align-items-start">
            <svg class="text-success flex-shrink-0 me-3 mt-1" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
              <h6 class="fw-bold text-success mb-1">Dokumen Terverifikasi</h6>
              <p class="text-muted mb-0 small">
                Dokumen ini telah diverifikasi dan terdaftar dalam sistem approval YPT.
              </p>
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
  layout: false,
})

const route = useRoute()
const config = useRuntimeConfig()

const documentId = computed(() => route.params.id as string)
const document = ref<Document | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const apiBase = config.public.apiBase

const loadDocumentInfo = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch(`${apiBase}/documents/${documentId.value}/public-info`)
    if (!response.ok) {
      const data = await response.json().catch(() => ({}))
      throw new Error(data.message || 'Dokumen tidak tersedia')
    }
    
    const info = await response.json()
    document.value = info.document
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
  })
}

onMounted(() => {
  loadDocumentInfo()
})
</script>
