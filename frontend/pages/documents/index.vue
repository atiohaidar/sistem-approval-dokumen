<template>
  <div>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Dokumen</h1>
        <p class="text-gray-600 mt-2">Kelola semua dokumen Anda</p>
      </div>
      <NuxtLink to="/documents/create" class="btn btn-primary">
        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Upload Dokumen Baru
      </NuxtLink>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="form-label">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="form-input"
            placeholder="Cari dokumen..."
            @input="handleSearch"
          />
        </div>
        <div>
          <label class="form-label">Status</label>
          <select v-model="filters.status" @change="loadDocuments" class="form-input">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="pending_approval">Pending Approval</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div>
          <label class="form-label">Created By</label>
          <select v-model="filters.created_by" @change="loadDocuments" class="form-input">
            <option value="">Semua User</option>
            <option :value="authStore.user?.id">Saya</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Documents List -->
    <div class="card">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-2">Loading...</p>
      </div>

      <div v-else-if="documents.length === 0" class="text-center py-8 text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p>Tidak ada dokumen ditemukan</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creator</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900">{{ doc.title }}</div>
                  <div class="text-sm text-gray-500">{{ doc.description || '-' }}</div>
                </td>
                <td class="px-4 py-3">
                  <span :class="getStatusClass(doc.status)">
                    {{ formatStatus(doc.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ doc.creator?.name || '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                  {{ formatDate(doc.created_at) }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex gap-2">
                    <NuxtLink :to="`/documents/${doc.id}`" class="text-telkom-blue hover:text-telkom-blue-light font-medium">
                      View
                    </NuxtLink>
                    <button
                      v-if="doc.created_by === authStore.user?.id && doc.status === 'draft'"
                      @click="handleDelete(doc.id)"
                      class="text-red-600 hover:text-red-800 font-medium"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-between mt-6">
          <div class="text-sm text-gray-600">
            Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
            {{ pagination.total }} results
          </div>
          <div class="flex gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="btn btn-secondary text-sm"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="btn btn-secondary text-sm"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useDocuments } from '~/composables/useDocuments'
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const authStore = useAuthStore()
const { getDocuments, deleteDocument } = useDocuments()

const documents = ref<Document[]>([])
const loading = ref(true)
const filters = reactive({
  search: '',
  status: '',
  created_by: '',
})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

let searchTimeout: NodeJS.Timeout

const loadDocuments = async () => {
  loading.value = true
  try {
    const params: any = {
      page: pagination.value.current_page,
    }
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    if (filters.created_by) params.created_by = filters.created_by

    const response = await getDocuments(params)
    documents.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total,
    }
  } catch (error) {
    console.error('Error loading documents:', error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadDocuments()
  }, 500)
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadDocuments()
}

const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) return

  try {
    await deleteDocument(id)
    loadDocuments()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal menghapus dokumen')
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
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  loadDocuments()
})
</script>
