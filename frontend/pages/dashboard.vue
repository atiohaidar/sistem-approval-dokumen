<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 p-6">
    <!-- Header with Animation -->
    <div class="mb-8 animate-fade-in-up">
      <h1 class="text-5xl font-black mb-2">
        <span class="text-gradient-telkom">Dashboard</span>
      </h1>
      <p class="text-gray-600 text-lg">Selamat datang di Sistem Approval Dokumen, {{ authStore.user?.name }}</p>
    </div>

    <!-- Statistics Cards with Glassmorphism -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Pending Approvals Card -->
      <GlassCard 
        :glow="true" 
        glow-color="telkom"
        class="group animate-fade-in-up"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Pending Approvals</p>
            <p class="text-4xl font-black text-gray-900 mt-3">{{ pendingCount }}</p>
            <p class="text-telkom-red text-xs mt-2 font-semibold">{{ pendingCount > 0 ? 'Butuh perhatian' : 'Semua clear!' }}</p>
          </div>
          <FloatingIcon icon="custom" color="telkom" size="md">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </FloatingIcon>
        </div>
        <div class="mt-4 h-1 bg-gradient-to-r from-telkom-red to-orange-500 rounded-full"></div>
      </GlassCard>

      <!-- My Documents Card -->
      <GlassCard 
        :glow="true" 
        glow-color="blue"
        class="group animate-fade-in-up delay-100"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">My Documents</p>
            <p class="text-4xl font-black text-gray-900 mt-3">{{ myDocumentsCount }}</p>
            <p class="text-blue-600 text-xs mt-2 font-semibold">Total dokumen Anda</p>
          </div>
          <FloatingIcon icon="custom" color="blue" size="md" delay="500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </FloatingIcon>
        </div>
        <div class="mt-4 h-1 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></div>
      </GlassCard>

      <!-- Completed Card -->
      <GlassCard 
        :glow="true" 
        glow-color="green"
        class="group animate-fade-in-up delay-200"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Completed</p>
            <p class="text-4xl font-black text-gray-900 mt-3">{{ completedCount }}</p>
            <p class="text-green-600 text-xs mt-2 font-semibold">Selesai diproses</p>
          </div>
          <FloatingIcon icon="check" color="green" size="md" delay="1000" />
        </div>
        <div class="mt-4 h-1 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full"></div>
      </GlassCard>

      <!-- Rejected Card -->
      <GlassCard 
        :glow="true" 
        glow-color="telkom"
        class="group animate-fade-in-up delay-300"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Rejected</p>
            <p class="text-4xl font-black text-gray-900 mt-3">{{ rejectedCount }}</p>
            <p class="text-red-600 text-xs mt-2 font-semibold">Perlu revisi</p>
          </div>
          <FloatingIcon icon="custom" color="telkom" size="md">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </FloatingIcon>
        </div>
        <div class="mt-4 h-1 bg-gradient-to-r from-red-500 to-pink-500 rounded-full"></div>
      </GlassCard>
    </div>

    <!-- Quick Actions -->
    <GlassCard class="mb-8 animate-fade-in-up delay-400">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-telkom-red to-orange-600 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Quick Actions</h2>
      </div>
      <div class="flex flex-wrap gap-4">
        <GradientButton 
          tag="NuxtLink" 
          to="/documents/create"
          variant="telkom"
          size="md"
        >
          <template #icon-left>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </template>
          Upload Dokumen Baru
        </GradientButton>
        
        <GradientButton 
          tag="NuxtLink" 
          to="/approvals"
          variant="blue"
          size="md"
        >
          <template #icon-left>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </template>
          Lihat Pending Approvals
        </GradientButton>

        <GradientButton 
          tag="NuxtLink" 
          to="/documents"
          variant="outline"
          size="md"
        >
          <template #icon-left>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </template>
          Semua Dokumen
        </GradientButton>
      </div>
    </GlassCard>

    <!-- Recent Documents -->
    <GlassCard class="animate-fade-in-up delay-500">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Recent Documents</h2>
        </div>
        <NuxtLink to="/documents" class="text-telkom-red hover:text-orange-500 font-semibold transition flex items-center gap-2">
          View All
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </NuxtLink>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-4 font-medium">Loading...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="recentDocuments.length === 0" class="text-center py-12">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-gray-700 font-medium">Tidak ada dokumen</p>
        <p class="text-gray-500 text-sm mt-2">Upload dokumen pertama Anda</p>
      </div>

      <!-- Documents Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Title</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Created</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="doc in recentDocuments" 
              :key="doc.id" 
              class="hover:bg-gray-50 transition-colors group"
            >
              <td class="px-4 py-4">
                <div class="font-semibold text-gray-900 group-hover:text-telkom-red transition">{{ doc.title }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ doc.description }}</div>
              </td>
              <td class="px-4 py-4">
                <span :class="getStatusClass(doc.status)" class="inline-flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(doc.status)"></span>
                  {{ formatStatus(doc.status) }}
                </span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-600">
                {{ formatDate(doc.created_at) }}
              </td>
              <td class="px-4 py-4">
                <NuxtLink 
                  :to="`/documents/${doc.id}`" 
                  class="inline-flex items-center gap-2 text-telkom-red hover:text-orange-500 font-semibold transition group"
                >
                  View
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </GlassCard>
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
    draft: 'glass-badge text-gray-700 bg-gray-500/20 border border-gray-500/30',
    pending_approval: 'glass-badge text-yellow-700 bg-yellow-500/20 border border-yellow-500/30',
    completed: 'glass-badge text-green-700 bg-green-500/20 border border-green-500/30',
    rejected: 'glass-badge text-red-700 bg-red-500/20 border border-red-500/30',
  }
  return classes[status] || 'glass-badge'
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
