<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Pending Approvals</h1>
      <p class="text-gray-600 mt-2">Dokumen yang menunggu approval dari Anda</p>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
      <p class="text-gray-600 mt-2">Loading...</p>
    </div>

    <div v-else-if="pendingApprovals.length === 0" class="card text-center py-12">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-gray-600 text-lg">Tidak ada dokumen yang menunggu approval</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="doc in pendingApprovals" :key="doc.id" class="card hover:shadow-lg transition-shadow">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ doc.title }}</h3>
            <p class="text-gray-600 mb-4">{{ doc.description || 'Tidak ada deskripsi' }}</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              <div>
                <p class="text-sm text-gray-500">Creator</p>
                <p class="text-sm font-medium text-gray-900">{{ doc.creator?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Created</p>
                <p class="text-sm font-medium text-gray-900">{{ formatDate(doc.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Current Level</p>
                <p class="text-sm font-medium text-gray-900">{{ doc.current_level }} / {{ doc.approvers?.length || 0 }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="badge badge-pending">Pending</span>
              </div>
            </div>

            <div class="flex gap-3">
              <NuxtLink :to="`/documents/${doc.id}`" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View & Process
              </NuxtLink>
              <button @click="quickApprove(doc.id)" class="btn btn-outline">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Quick Approve
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Approve Modal -->
    <div v-if="showQuickApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Approve</h3>
        <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menyetujui dokumen ini?</p>
        <div class="mb-4">
          <label class="form-label">Comments (opsional)</label>
          <textarea
            v-model="quickApproveComments"
            class="form-input"
            rows="3"
            placeholder="Tambahkan komentar..."
          ></textarea>
        </div>
        <div class="flex gap-4">
          <button @click="confirmQuickApprove" class="btn btn-primary flex-1" :disabled="processing">
            {{ processing ? 'Processing...' : 'Approve' }}
          </button>
          <button @click="showQuickApproveModal = false" class="btn btn-secondary flex-1">
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useApprovals } from '~/composables/useApprovals'
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { usePendingApprovalsQuery, useProcessApprovalMutation } = useApprovals()

// Query for pending approvals
const { data: pendingApprovals, isLoading: loading } = usePendingApprovalsQuery()

// Mutation for processing approval
const processApprovalMutation = useProcessApprovalMutation()

const processing = computed(() => processApprovalMutation.isPending.value)
const showQuickApproveModal = ref(false)
const quickApproveComments = ref('')
const selectedDocumentId = ref<number | null>(null)

const quickApprove = (documentId: number) => {
  selectedDocumentId.value = documentId
  showQuickApproveModal.value = true
}

const confirmQuickApprove = async () => {
  if (!selectedDocumentId.value) return

  try {
    await processApprovalMutation.mutateAsync({
      documentId: selectedDocumentId.value,
      data: {
        action: 'approve',
        comments: quickApproveComments.value || null,
      },
    })
    showQuickApproveModal.value = false
    quickApproveComments.value = ''
    selectedDocumentId.value = null
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal approve dokumen')
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
