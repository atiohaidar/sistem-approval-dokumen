<template>
  <div
    class="min-h-screen p-6 transition-colors duration-300"
    :class="isDark ? 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900' : 'bg-gradient-to-br from-blue-50 via-white to-orange-50'"
  >
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between animate-fade-in-up">
      <div>
        <h1 class="text-5xl font-black mb-2">
          <span class="text-gradient-telkom">Dokumen</span>
        </h1>
        <p class="text-lg" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Kelola semua dokumen Anda</p>
      </div>
      <GradientButton
        tag="NuxtLink"
        to="/documents/create"
        variant="telkom"
        size="lg"
        :show-arrow="true"
      >
        <template #icon-left>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </template>
        Upload Dokumen Baru
      </GradientButton>
    </div>

    <!-- Filters -->
    <GlassCard
      class="mb-6 animate-fade-in-up delay-100 transition-colors"
      :class="isDark ? 'bg-gray-800/80 border border-gray-700 text-gray-100' : 'bg-white/80'">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Filter & Search</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-500'">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="glass-input w-full"
            :class="isDark ? 'bg-gray-900/40 border border-gray-700 text-gray-100 placeholder-gray-500' : ''"
            placeholder="Cari dokumen..."
            @input="handleSearch"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-500'">Status</label>
          <select
            v-model="filters.status"
            @change="loadDocuments"
            class="glass-input w-full"
            :class="isDark ? 'bg-gray-900/40 border border-gray-700 text-gray-100' : ''"
          >
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="pending_approval">Pending Approval</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">Created By</label>
          <select
            v-model="filters.created_by"
            @change="loadDocuments"
            class="glass-input w-full"
            :class="isDark ? 'bg-gray-900/40 border border-gray-700 text-gray-100' : ''"
          >
            <option value="">Semua User</option>
            <option :value="authStore.user?.id">Saya</option>
          </select>
        </div>
      </div>
    </GlassCard>

    <!-- Documents List -->
    <GlassCard
      class="animate-fade-in-up delay-200 transition-colors"
      :class="isDark ? 'bg-gray-800/80 border border-gray-700 text-gray-100' : 'bg-white/80'"
    >
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16">
        <div
          class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-t-transparent"
          :class="isDark ? 'border-red-500' : 'border-telkom-red'"
        ></div>
        <p class="mt-4 font-medium text-lg" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Loading documents...</p>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="documents.length === 0"
        class="text-center py-16 rounded-2xl border transition-all"
        :class="isDark ? 'bg-gray-900/50 border-gray-700' : 'bg-gray-50 border-white'"
      >
        <div
          class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6"
          :class="isDark ? 'bg-gray-800 text-gray-500' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400'"
        >
          <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-xl font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-500'">Tidak ada dokumen ditemukan</p>
        <p :class="isDark ? 'text-gray-500' : 'text-gray-500'">Coba ubah filter atau upload dokumen baru</p>
        <GradientButton 
          tag="NuxtLink" 
          to="/documents/create"
          variant="telkom"
          size="md"
          class="mt-6"
        >
          Upload Dokumen
        </GradientButton>
      </div>

      <!-- Documents Table -->
      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b" :class="isDark ? 'border-gray-700' : 'border-gray-200'">
                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Title</th>
                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Status</th>
                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Creator</th>
                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Created</th>
                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-700' : 'divide-gray-100'">
              <tr 
                v-for="doc in documents" 
                :key="doc.id" 
                class="transition-colors group"
                :class="isDark ? 'hover:bg-gray-800/60' : 'hover:bg-gray-50'"
              >
                <td class="px-4 py-4">
                  <div class="font-semibold transition" :class="isDark ? 'text-white group-hover:text-red-400' : 'text-gray-900 group-hover:text-telkom-red'">{{ doc.title }}</div>
                  <div class="text-sm mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-600'">{{ doc.description || '-' }}</div>
                </td>
                <td class="px-4 py-4">
                  <span :class="getStatusClass(doc.status)" class="inline-flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(doc.status)"></span>
                    {{ formatStatus(doc.status) }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold"
                      :class="isDark ? 'bg-blue-500/40 text-blue-200' : 'bg-gradient-to-br from-blue-500 to-cyan-500 text-white'"
                    >
                      {{ doc.creator?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <span class="text-sm" :class="isDark ? 'text-gray-200' : 'text-gray-700'">{{ doc.creator?.name || '-' }}</span>
                  </div>
                </td>
                <td class="px-4 py-4 text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                  {{ formatDate(doc.created_at) }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex gap-3">
                    <NuxtLink 
                      :to="`/documents/${doc.id}`" 
                      class="inline-flex items-center gap-1 font-semibold transition group"
                      :class="isDark ? 'text-blue-300 hover:text-blue-200' : 'text-blue-600 hover:text-blue-700'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      View
                    </NuxtLink>
                    <button
                      v-if="doc.created_by === authStore.user?.id && doc.status === 'draft'"
                      @click="handleDelete(doc.id)"
                      class="inline-flex items-center gap-1 font-semibold transition"
                      :class="isDark ? 'text-red-300 hover:text-red-200' : 'text-red-600 hover:text-red-700'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="pagination.last_page > 1"
          class="flex items-center justify-between mt-8 pt-6 border-t"
          :class="isDark ? 'border-gray-700' : 'border-gray-200'"
        >
          <div class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
            Showing <span class="font-semibold" :class="isDark ? 'text-gray-100' : 'text-gray-900'">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span> to 
            <span class="font-semibold" :class="isDark ? 'text-gray-100' : 'text-gray-900'">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> of 
            <span class="font-semibold" :class="isDark ? 'text-gray-100' : 'text-gray-900'">{{ pagination.total }}</span> results
          </div>
          <div class="flex gap-2">
            <GradientButton
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              variant="glass"
              size="sm"
            >
              Previous
            </GradientButton>
            
            <!-- Page Numbers -->
            <div class="flex gap-1">
              <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="changePage(page)"
                class="px-3 py-2 rounded-lg text-sm font-semibold transition"
                :class="page === pagination.current_page 
                  ? (isDark ? 'bg-gradient-to-r from-red-500 to-orange-500 text-white shadow-lg shadow-red-900/40' : 'bg-gradient-to-r from-telkom-red to-orange-600 text-white shadow-md') 
                  : (isDark ? 'bg-white/5 text-gray-300 hover:bg-white/10' : 'glass text-gray-500 hover:text-gray-900')"
              >
                {{ page }}
              </button>
            </div>

            <GradientButton
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              variant="glass"
              size="sm"
            >
              Next
            </GradientButton>
          </div>
        </div>
      </div>
    </GlassCard>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useDocuments } from '~/composables/useDocuments'
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { isDark } = useTheme()
const authStore = useAuthStore()
const { useDocumentsQuery, useDeleteDocumentMutation } = useDocuments()

const filters = reactive({
  search: '',
  status: '',
  created_by: '',
})
const currentPage = ref(1)

// Build query params reactively
const queryParams = computed(() => {
  const params: any = {
    page: currentPage.value,
  }
  if (filters.search) params.search = filters.search
  if (filters.status) params.status = filters.status
  if (filters.created_by) params.created_by = filters.created_by
  return params
})

// Query for documents with reactive params
const { data: docsData, isLoading: loading, refetch } = useDocumentsQuery(queryParams)

const documents = computed(() => docsData.value?.data || [])
const pagination = computed(() => ({
  current_page: docsData.value?.current_page || 1,
  last_page: docsData.value?.last_page || 1,
  per_page: docsData.value?.per_page || 15,
  total: docsData.value?.total || 0,
}))

// Delete mutation
const deleteMutation = useDeleteDocumentMutation()

let searchTimeout: ReturnType<typeof setTimeout>

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    refetch()
  }, 500)
}

const loadDocuments = () => {
  currentPage.value = 1
  refetch()
}

const changePage = (page: number) => {
  currentPage.value = page
  refetch()
}

const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) return

  try {
    await deleteMutation.mutateAsync(id)
    refetch()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal menghapus dokumen')
  }
}

const getStatusClass = (status: string) => {
  const lightClasses: Record<string, string> = {
    draft: 'glass-badge text-gray-700 bg-gray-500/20 border border-gray-500/30',
    pending_approval: 'glass-badge text-yellow-700 bg-yellow-500/20 border border-yellow-500/30',
    completed: 'glass-badge text-green-700 bg-green-500/20 border border-green-500/30',
    rejected: 'glass-badge text-red-700 bg-red-500/20 border border-red-500/30',
  }

  const darkClasses: Record<string, string> = {
    draft: 'glass-badge bg-gray-700/40 text-gray-200 border border-gray-500/40',
    pending_approval: 'glass-badge bg-yellow-500/20 text-yellow-300 border border-yellow-500/30',
    completed: 'glass-badge bg-green-500/20 text-green-300 border border-green-500/30',
    rejected: 'glass-badge bg-red-500/20 text-red-300 border border-red-500/40',
  }

  const map = isDark.value ? darkClasses : lightClasses
  return map[status] || 'glass-badge'
}

const getStatusDotClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'bg-gray-500 animate-pulse',
    pending_approval: 'bg-yellow-500 animate-pulse',
    completed: 'bg-green-500',
    rejected: 'bg-red-500',
  }
  return classes[status] || 'bg-gray-500'
}

const getPageNumbers = () => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, pagination.value.current_page - 2)
  let end = Math.min(pagination.value.last_page, start + maxVisible - 1)
  
  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
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
    month: 'short',
    day: 'numeric',
  })
}
</script>
