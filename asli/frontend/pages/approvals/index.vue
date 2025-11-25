<template>
  <div
    class="min-h-screen p-6 transition-colors duration-300"
    :class="isDark ? 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900' : 'bg-gradient-to-br from-blue-50 via-white to-orange-50'"
  >
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <h1 class="text-4xl font-black" :class="isDark ? 'text-white' : 'text-gray-900'">Pending Approvals</h1>
        <p class="mt-2 text-lg" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Dokumen yang menunggu approval dari Anda</p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-t-transparent"
          :class="isDark ? 'border-red-500' : 'border-telkom-red'"
        ></div>
        <p class="mt-4 font-medium" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Loading...</p>
      </div>

      <div
        v-else-if="pendingList.length === 0"
        class="rounded-2xl text-center py-16 shadow-xl transition-all"
        :class="isDark ? 'bg-gray-800/80 border border-gray-700 text-gray-100' : 'bg-white text-gray-700'"
      >
        <svg class="w-16 h-16 mx-auto mb-6" :class="isDark ? 'text-gray-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-lg font-semibold" :class="isDark ? 'text-gray-300' : 'text-gray-600'">Tidak ada dokumen yang menunggu approval</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="doc in pendingList"
          :key="doc.id"
          class="rounded-2xl p-6 transition-all border"
          :class="isDark ? 'bg-gray-800/80 border-gray-700 hover:bg-gray-700/70 shadow-xl shadow-black/30' : 'bg-white border-white/60 shadow-md hover:shadow-xl'"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-2xl font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">{{ doc.title }}</h3>
              <p class="mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ doc.description || 'Tidak ada deskripsi' }}</p>

              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                  <p class="text-sm font-semibold uppercase tracking-wide" :class="isDark ? 'text-gray-500' : 'text-gray-500'">Creator</p>
                  <p class="text-sm font-medium" :class="isDark ? 'text-gray-200' : 'text-gray-900'">{{ doc.creator?.name || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm font-semibold uppercase tracking-wide" :class="isDark ? 'text-gray-500' : 'text-gray-500'">Created</p>
                  <p class="text-sm font-medium" :class="isDark ? 'text-gray-200' : 'text-gray-900'">{{ formatDate(doc.created_at) }}</p>
                </div>
                <div>
                  <p class="text-sm font-semibold uppercase tracking-wide" :class="isDark ? 'text-gray-500' : 'text-gray-500'">Current Level</p>
                  <p class="text-sm font-medium" :class="isDark ? 'text-gray-200' : 'text-gray-900'">{{ doc.current_level }} / {{ doc.approvers?.length || 0 }}</p>
                </div>
                <div>
                  <p class="text-sm font-semibold uppercase tracking-wide" :class="isDark ? 'text-gray-500' : 'text-gray-500'">Status</p>
                  <span class="badge" :class="isDark ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'badge-pending'">Pending</span>
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <NuxtLink :to="`/documents/${doc.id}`" class="btn btn-primary">
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  View & Process
                </NuxtLink>
                <button
                  @click="quickApprove(doc.id)"
                  class="btn btn-outline"
                  :class="isDark ? 'border-red-400 text-red-300 hover:bg-red-500/20 hover:text-red-200' : ''"
                >
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
    </div>

    <!-- Quick Approve Modal -->
    <div
      v-if="showQuickApproveModal"
      class="fixed inset-0 flex items-center justify-center z-50 transition"
      :class="isDark ? 'bg-black/70 backdrop-blur-sm' : 'bg-black/50'"
    >
      <div
        class="w-full max-w-md mx-4 rounded-2xl p-6 shadow-2xl transition"
        :class="isDark ? 'bg-gray-900 border border-gray-700 text-gray-100' : 'bg-white text-gray-900'"
      >
        <h3 class="text-xl font-bold mb-4" :class="isDark ? 'text-white' : 'text-gray-800'">Quick Approve</h3>
        <p class="mb-4" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Apakah Anda yakin ingin menyetujui dokumen ini?</p>
        <div class="mb-4">
          <label class="form-label" :class="isDark ? 'text-gray-300' : ''">Comments (opsional)</label>
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
          <button
            @click="showQuickApproveModal = false"
            class="btn btn-secondary flex-1"
            :class="isDark ? 'bg-gray-700 hover:bg-gray-600 text-white' : ''"
          >
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

const { isDark } = useTheme()
const { usePendingApprovalsQuery, useProcessApprovalMutation } = useApprovals()

// Query for pending approvals
const { data: pendingApprovals, isLoading: loading } = usePendingApprovalsQuery()
const pendingList = computed(() => pendingApprovals.value ?? [])

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
