<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-4">Memverifikasi akses...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen">
      <div class="max-w-md w-full mx-auto px-4">
        <div class="card text-center">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Akses Ditolak</h2>
          <p class="text-gray-600 mb-6">{{ error }}</p>
          <NuxtLink to="/" class="btn btn-primary inline-block">
            Kembali ke Beranda
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Success State -->
    <div v-else-if="document" class="py-8">
      <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between mb-4">
            <h1 class="text-3xl font-bold text-gray-800">{{ document.title }}</h1>
            <span :class="getStatusClass(document.status)">
              {{ formatStatus(document.status) }}
            </span>
          </div>
          <p class="text-gray-600">{{ document.description || 'Tidak ada deskripsi' }}</p>
          
          <!-- Token Expiration Warning -->
          <div v-if="tokenExpiresAt" class="mt-4 flex items-center gap-2 text-sm">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-gray-600">
              Link akses ini akan kedaluwarsa pada 
              <strong class="text-gray-900">{{ formatDate(tokenExpiresAt) }}</strong>
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Document Preview -->
            <div class="card">
              <h2 class="text-xl font-bold text-gray-800 mb-4">Preview Dokumen</h2>
              <div class="bg-gray-100 rounded-lg overflow-hidden" style="height: 600px;">
                <iframe 
                  :src="previewUrl" 
                  class="w-full h-full border-0"
                  title="Document Preview"
                ></iframe>
              </div>
              <div class="mt-4 flex gap-3">
                <button @click="handleDownload" class="btn btn-primary flex-1">
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  Download Dokumen
                </button>
              </div>
            </div>

            <!-- Document Info -->
            <div class="card">
              <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokumen</h2>
              <dl class="grid grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Creator</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ document.creator?.name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">File Name</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ document.file_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Created At</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(document.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatStatus(document.status) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Approval Progress -->
            <div class="card">
              <h2 class="text-xl font-bold text-gray-800 mb-4">Progress Persetujuan</h2>
              <div v-if="approvalProgress && Object.keys(approvalProgress).length > 0" class="space-y-4">
                <div 
                  v-for="(levelData, level) in approvalProgress" 
                  :key="level"
                  class="border-l-4 pl-4"
                  :class="getLevelBorderClass(levelData.status)"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-gray-900">Level {{ level }}</span>
                    <span 
                      class="text-xs px-2 py-1 rounded-full"
                      :class="getProgressStatusClass(levelData.status)"
                    >
                      {{ formatProgressStatus(levelData.status) }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-600">
                    <div v-if="levelData.approved && levelData.approved.length > 0">
                      ✓ {{ levelData.approved.length }} disetujui
                    </div>
                    <div v-if="levelData.pending && levelData.pending.length > 0">
                      ⏳ {{ levelData.pending.length }} menunggu
                    </div>
                    <div v-if="levelData.rejected && levelData.rejected.length > 0">
                      ✗ {{ levelData.rejected.length }} ditolak
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                Belum ada proses persetujuan
              </div>
            </div>

            <!-- Security Info -->
            <div class="card bg-blue-50 border border-blue-200">
              <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                  <h3 class="font-semibold text-blue-900 mb-1">Akses Aman</h3>
                  <p class="text-sm text-blue-800">
                    Dokumen ini diakses menggunakan token akses aman dengan batas waktu. 
                    Jangan bagikan link ini kepada pihak yang tidak berwenang.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const token = route.params.token

const loading = ref(true)
const error = ref(null)
const document = ref(null)
const approvalProgress = ref(null)
const tokenExpiresAt = ref(null)

const config = useRuntimeConfig()
const apiBase = config.public.apiBase

const previewUrl = computed(() => {
  if (!token) return ''
  return `${apiBase}/secure/documents/${token}/preview`
})

onMounted(async () => {
  try {
    const response = await fetch(`${apiBase}/secure/documents/${token}`)
    
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Token akses tidak valid atau telah kedaluwarsa')
    }

    const data = await response.json()
    document.value = data.document
    approvalProgress.value = data.approval_progress
    tokenExpiresAt.value = data.token_expires_at
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
})

const handleDownload = () => {
  window.open(`${apiBase}/secure/documents/${token}/download`, '_blank')
}

const getStatusClass = (status) => {
  const classes = {
    'draft': 'badge badge-gray',
    'pending_approval': 'badge badge-yellow',
    'completed': 'badge badge-green',
    'rejected': 'badge badge-red',
    'cancelled': 'badge badge-gray'
  }
  return classes[status] || 'badge badge-gray'
}

const formatStatus = (status) => {
  const statuses = {
    'draft': 'Draft',
    'pending_approval': 'Menunggu Persetujuan',
    'completed': 'Selesai',
    'rejected': 'Ditolak',
    'cancelled': 'Dibatalkan'
  }
  return statuses[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getLevelBorderClass = (status) => {
  const classes = {
    'completed': 'border-green-500',
    'in_progress': 'border-blue-500',
    'pending': 'border-gray-300',
    'rejected': 'border-red-500'
  }
  return classes[status] || 'border-gray-300'
}

const getProgressStatusClass = (status) => {
  const classes = {
    'completed': 'bg-green-100 text-green-800',
    'in_progress': 'bg-blue-100 text-blue-800',
    'pending': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatProgressStatus = (status) => {
  const statuses = {
    'completed': 'Selesai',
    'in_progress': 'Berlangsung',
    'pending': 'Menunggu',
    'rejected': 'Ditolak'
  }
  return statuses[status] || status
}
</script>

<style scoped>
.badge {
  @apply px-3 py-1 rounded-full text-sm font-medium;
}

.badge-gray {
  @apply bg-gray-100 text-gray-800;
}

.badge-yellow {
  @apply bg-yellow-100 text-yellow-800;
}

.badge-green {
  @apply bg-green-100 text-green-800;
}

.badge-red {
  @apply bg-red-100 text-red-800;
}

.card {
  @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.btn {
  @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-primary {
  @apply bg-telkom-red text-white hover:bg-telkom-red-dark;
}
</style>
