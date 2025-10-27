<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-telkom-red mb-2">Telkom Indonesia</h1>
        <p class="text-gray-600">Informasi Dokumen</p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-4">Loading...</p>
      </div>

      <div v-else-if="document" class="space-y-6">
        <!-- Document Info Card -->
        <div class="card">
          <div class="flex items-start justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">{{ document.title }}</h2>
              <p class="text-gray-600 mt-2">{{ document.description || 'Tidak ada deskripsi' }}</p>
            </div>
            <span :class="getStatusClass(document.status)">
              {{ formatStatus(document.status) }}
            </span>
          </div>

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

        <!-- Approval Progress Card -->
        <div class="card">
          <h3 class="text-xl font-bold text-gray-800 mb-4">Approval Progress</h3>
          
          <div class="space-y-4">
            <div
              v-for="(level, levelNumber) in approvalLevels"
              :key="levelNumber"
              class="p-4 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-700">Level {{ levelNumber }}</h4>
                <span :class="getLevelStatusBadge(level.status)">
                  {{ formatLevelStatus(level.status) }}
                </span>
              </div>

              <div class="space-y-2">
                <div
                  v-for="approver in level.approvers"
                  :key="approver.id"
                  class="flex items-center justify-between p-3 bg-white rounded"
                >
                  <div v-if="approver.user" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-telkom-grey rounded-full flex items-center justify-center text-white font-medium">
                      {{ getInitials(approver.user.name) }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ approver.user.name }}</p>
                      <p class="text-sm text-gray-500">{{ approver.user.email }}</p>
                    </div>
                  </div>
                  <span :class="getApproverStatusBadge(approver.status)">
                    {{ formatApproverStatus(approver.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Verification Info -->
        <div class="card bg-green-50 border-2 border-green-500">
          <div class="flex items-start space-x-3">
            <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div>
              <h4 class="font-bold text-green-800 mb-1">Dokumen Terverifikasi</h4>
              <p class="text-sm text-green-700">
                Dokumen ini telah diverifikasi dan terdaftar dalam sistem approval Telkom Indonesia.
                Informasi di atas menunjukkan status terkini dari proses approval dokumen.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="card text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-gray-600 text-lg">Dokumen tidak ditemukan</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useDocuments } from '~/composables/useDocuments'
import type { Document, PublicDocumentInfo, ApprovalLevel } from '~/types/api'

definePageMeta({
  layout: false,
})

const route = useRoute()
const { getPublicInfo } = useDocuments()

const document = ref<Document | null>(null)
const approvalLevels = ref<Record<number, ApprovalLevel>>({})
const loading = ref(true)

const loadDocumentInfo = async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)
    const info: PublicDocumentInfo = await getPublicInfo(id)
    document.value = info.document
    approvalLevels.value = info.approval_levels
  } catch (error) {
    console.error('Error loading document info:', error)
  } finally {
    loading.value = false
  }
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
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

const getLevelStatusBadge = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'badge badge-approved',
    in_progress: 'badge badge-pending',
    pending: 'badge',
    rejected: 'badge badge-rejected',
  }
  return classes[status] || 'badge'
}

const getApproverStatusBadge = (status: string) => {
  const classes: Record<string, string> = {
    approved: 'badge badge-approved',
    pending: 'badge badge-pending',
    rejected: 'badge badge-rejected',
    skipped: 'badge',
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

const formatLevelStatus = (status: string) => {
  const labels: Record<string, string> = {
    completed: 'Completed',
    in_progress: 'In Progress',
    pending: 'Pending',
    rejected: 'Rejected',
  }
  return labels[status] || status
}

const formatApproverStatus = (status: string) => {
  const labels: Record<string, string> = {
    approved: 'Approved',
    pending: 'Pending',
    rejected: 'Rejected',
    skipped: 'Skipped',
    cancelled: 'Cancelled',
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

onMounted(() => {
  loadDocumentInfo()
})
</script>
