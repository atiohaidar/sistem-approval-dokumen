<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-gray-600 mt-2">Selamat datang di Sistem Approval Dokumen</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm">Pending Approvals</p>
            <p class="text-3xl font-bold text-telkom-red mt-2">{{ pendingCount }}</p>
          </div>
          <div class="bg-telkom-red bg-opacity-10 p-3 rounded-lg">
            <svg class="w-8 h-8 text-telkom-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm">My Documents</p>
            <p class="text-3xl font-bold text-telkom-blue mt-2">{{ myDocumentsCount }}</p>
          </div>
          <div class="bg-telkom-blue bg-opacity-10 p-3 rounded-lg">
            <svg class="w-8 h-8 text-telkom-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm">Completed</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ completedCount }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-500 text-sm">Rejected</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ rejectedCount }}</p>
          </div>
          <div class="bg-red-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-8">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
      <div class="flex gap-4">
        <NuxtLink to="/documents/create" class="btn btn-primary">
          <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Upload Dokumen Baru
        </NuxtLink>
        <NuxtLink to="/approvals" class="btn btn-outline">
          <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          Lihat Pending Approvals
        </NuxtLink>
      </div>
    </div>

    <!-- Recent Documents -->
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Recent Documents</h2>
        <NuxtLink to="/documents" class="text-telkom-red hover:text-telkom-red-dark font-medium">
          View All →
        </NuxtLink>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-2">Loading...</p>
      </div>

      <div v-else-if="recentDocuments.length === 0" class="text-center py-8 text-gray-500">
        Tidak ada dokumen
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="doc in recentDocuments" :key="doc.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ doc.title }}</div>
                <div class="text-sm text-gray-500">{{ doc.description }}</div>
              </td>
              <td class="px-4 py-3">
                <span :class="getStatusClass(doc.status)">
                  {{ formatStatus(doc.status) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">
                {{ formatDate(doc.created_at) }}
              </td>
              <td class="px-4 py-3">
                <NuxtLink :to="`/documents/${doc.id}`" class="text-telkom-red hover:text-telkom-red-dark font-medium">
                  View
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useDocuments } from '~/composables/useDocuments'
import { useApprovals } from '~/composables/useApprovals'
import type { Document } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const authStore = useAuthStore()
const { getDocuments } = useDocuments()
const { getPendingApprovals } = useApprovals()

const recentDocuments = ref<Document[]>([])
const loading = ref(true)
const pendingCount = ref(0)
const myDocumentsCount = ref(0)
const completedCount = ref(0)
const rejectedCount = ref(0)

onMounted(async () => {
  try {
    // Load recent documents
    const docsResponse = await getDocuments({ page: 1 })
    recentDocuments.value = docsResponse.data.slice(0, 5)
    
    // Load statistics
    const pendingApprovals = await getPendingApprovals()
    pendingCount.value = pendingApprovals.length
    
    // Count by status
    const allDocs = await getDocuments({ created_by: authStore.user?.id })
    myDocumentsCount.value = allDocs.data.length
    completedCount.value = allDocs.data.filter((d: Document) => d.status === 'completed').length
    rejectedCount.value = allDocs.data.filter((d: Document) => d.status === 'rejected').length
  } catch (error) {
    console.error('Error loading dashboard:', error)
  } finally {
    loading.value = false
  }
})

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'badge badge-pending',
    pending_approval: 'badge badge-pending',
    completed: 'badge badge-completed',
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
</script>
