<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md">
      <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="text-2xl font-bold text-telkom-red">
            Telkom Indonesia
          </div>
          <div class="text-gray-600">|</div>
          <div class="text-lg font-medium text-gray-700">
            Sistem Approval Dokumen
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-gray-700">{{ user?.name }}</span>
          <span v-if="isAdmin" class="badge bg-telkom-blue text-white">Admin</span>
          <button @click="handleLogout" class="btn btn-outline text-sm">
            Logout
          </button>
        </div>
      </div>
    </header>

    <div class="flex-1 flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-md">
        <nav class="p-4">
          <NuxtLink
            to="/dashboard"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition-colors"
            :class="{ 'bg-telkom-red text-white hover:bg-telkom-red-dark': $route.path === '/dashboard' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="font-medium">Dashboard</span>
          </NuxtLink>

          <NuxtLink
            to="/documents"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition-colors mt-2"
            :class="{ 'bg-telkom-red text-white hover:bg-telkom-red-dark': $route.path.startsWith('/documents') }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="font-medium">Dokumen</span>
          </NuxtLink>

          <NuxtLink
            to="/approvals"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition-colors mt-2"
            :class="{ 'bg-telkom-red text-white hover:bg-telkom-red-dark': $route.path.startsWith('/approvals') }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">Approval</span>
          </NuxtLink>

          <NuxtLink
            v-if="isAdmin"
            to="/users"
            class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 transition-colors mt-2"
            :class="{ 'bg-telkom-red text-white hover:bg-telkom-red-dark': $route.path.startsWith('/users') }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="font-medium">Users</span>
          </NuxtLink>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-8 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const { user, isAdmin } = storeToRefs(authStore)

const handleLogout = async () => {
  await authStore.logout()
}
</script>
