<template>
  <div class="min-h-screen flex flex-col" :class="isDark ? 'bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900' : 'bg-gradient-to-br from-blue-50 via-white to-orange-50'">
    <!-- Header with Glassmorphism -->
    <header class="glass-header fixed top-0 left-0 right-0 z-50" :class="isDark ? 'border-b border-white/10' : 'border-b border-gray-200'">
      <div class="container mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Hamburger Menu & Logo -->
        <div class="flex items-center space-x-4">
          <!-- Hamburger Button -->
          <button 
            @click="toggleSidebar"
            class="p-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden"
            :class="isDark ? 'text-white' : 'text-gray-900'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Desktop Hamburger (untuk hide/show sidebar) -->
          <button 
            @click="toggleSidebar"
            class="hidden lg:block p-2 rounded-lg transition-colors"
            :class="isDark ? 'text-white hover:bg-white/10' : 'text-gray-900 hover:bg-gray-100'"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <NuxtLink to="/dashboard" class="relative group" aria-label="Beranda YPT">
            <div class="absolute inset-0 bg-telkom-red rounded-xl blur-lg opacity-40 group-hover:opacity-70 transition"></div>
            <img
              src="/logo.png"
              alt="Logo YPT"
              class="relative w-12 h-12 rounded-xl shadow-lg bg-white p-1 object-contain"
            />
          </NuxtLink>
          <div>
            <div class="text-lg font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">YPT</div>
            <div class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Sistem Approval Dokumen</div>
          </div>
        </div>

        <!-- User Info, Dark Mode Toggle & Logout -->
        <div class="flex items-center space-x-3">
          <!-- User Info -->
          <div class="hidden md:block text-right">
            <div class="font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ user?.name }}</div>
            <div class="text-sm" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ user?.email }}</div>
          </div>
          
          <!-- Admin Badge -->
          <span 
            v-if="isAdmin" 
            class="glass-badge bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border-blue-500/30 font-semibold"
            :class="isDark ? 'text-blue-300' : 'text-blue-600'"
          >
            Admin
          </span>

          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="p-2 rounded-lg transition-all"
            :class="isDark ? 'bg-white/10 text-yellow-300 hover:bg-white/20' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            title="Toggle Dark Mode"
          >
            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all"
            :class="isDark 
              ? 'bg-red-500/20 text-red-300 hover:bg-red-500/30 border border-red-500/30' 
              : 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </div>
    </header>

    <div class="flex-1 flex pt-20">
      <!-- Sidebar with Glassmorphism -->
      <aside 
        class="glass-sidebar fixed left-0 top-20 bottom-0 w-64 overflow-y-auto transition-transform duration-300 z-40"
        :class="[
          isDark ? 'border-r border-white/10' : 'border-r border-gray-200',
          isSidebarOpen ? 'translate-x-0 lg:translate-x-0' : '-translate-x-full lg:-translate-x-full'
        ]"
      >
        <nav class="p-4 space-y-2">
          <NuxtLink
            to="/dashboard"
            class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
            :class="$route.path === '/dashboard' 
              ? 'bg-gradient-to-r from-telkom-red to-orange-600 text-white shadow-lg shadow-telkom-red/50' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="font-semibold">Dashboard</span>
            <svg 
              v-if="$route.path === '/dashboard'"
              class="w-4 h-4 ml-auto" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/documents"
            class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
            :class="$route.path.startsWith('/documents')
              ? 'bg-gradient-to-r from-telkom-red to-orange-600 text-white shadow-lg shadow-telkom-red/50' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="font-semibold">Dokumen</span>
            <svg 
              v-if="$route.path.startsWith('/documents')"
              class="w-4 h-4 ml-auto" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            to="/approvals"
            class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
            :class="$route.path.startsWith('/approvals')
              ? 'bg-gradient-to-r from-telkom-red to-orange-600 text-white shadow-lg shadow-telkom-red/50' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">Approval</span>
            <svg 
              v-if="$route.path.startsWith('/approvals')"
              class="w-4 h-4 ml-auto" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <NuxtLink
            v-if="isAdmin"
            to="/users"
            class="group flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
            :class="[$route.path.startsWith('/users')
              ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white shadow-lg shadow-blue-500/50' 
              : isDark ? 'text-gray-300 hover:bg-white/5 hover:text-white' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900']"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="font-semibold">Users</span>
            <svg 
              v-if="$route.path.startsWith('/users')"
              class="w-4 h-4 ml-auto" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NuxtLink>

          <!-- Divider -->
          <div class="divider-gradient my-4"></div>

          <!-- Quick Stats in Sidebar -->
          <div class="glass rounded-xl p-4 mt-4">
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Quick Stats</h3>
            <div class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span :class="isDark ? 'text-gray-400' : 'text-gray-600'">Active Docs</span>
                <span class="font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">-</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span :class="isDark ? 'text-gray-400' : 'text-gray-600'">Pending</span>
                <span class="font-bold" :class="isDark ? 'text-yellow-400' : 'text-yellow-600'">-</span>
              </div>
            </div>
          </div>
        </nav>
      </aside>

      <!-- Overlay untuk mobile -->
      <div 
        v-if="isSidebarOpen"
        @click="closeSidebar"
        class="fixed inset-0 bg-black/50 z-30 lg:hidden"
      ></div>

      <!-- Main Content -->
      <main 
        class="flex-1 overflow-auto transition-all duration-300"
        :class="isSidebarOpen ? 'lg:ml-64' : 'lg:ml-0'"
      >
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const { user, isAdmin } = storeToRefs(authStore)

// Use global theme composable instead of local state
const { isDark, toggleDarkMode, initTheme } = useTheme()

// Sidebar state
const isSidebarOpen = ref(true)

// Initialize theme on mount
onMounted(() => {
  initTheme()

  // Set initial sidebar state based on viewport
  if (window.innerWidth < 1024) {
    isSidebarOpen.value = false
  }

  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

// Close sidebar (untuk mobile)
const closeSidebar = () => {
  isSidebarOpen.value = false
}

const handleResize = () => {
  if (window.innerWidth >= 1024) {
    isSidebarOpen.value = true
  }
}

const handleLogout = async () => {
  await authStore.logout()
}
</script>
