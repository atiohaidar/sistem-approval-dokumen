<script setup lang="ts">
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const isSidebarOpen = ref(true)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const isActive = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

onMounted(() => {
  if (typeof window !== 'undefined' && window.innerWidth < 992) {
    isSidebarOpen.value = false
  }
})
</script>

<template>
  <div class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
      <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
          <button class="btn btn-link text-dark p-2 me-2" @click="toggleSidebar">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <NuxtLink to="/dashboard" class="navbar-brand d-flex align-items-center">
            <img src="/logo.png" alt="Logo" width="32" height="32" class="me-2" />
            <span class="fw-bold text-primary">YPT</span>
          </NuxtLink>
        </div>

        <div class="d-flex align-items-center gap-3">
          <div class="text-end d-none d-md-block">
            <div class="fw-medium small">{{ authStore.user?.name }}</div>
            <div class="text-muted smaller" style="font-size: 0.75rem;">{{ authStore.user?.email }}</div>
          </div>
          <span v-if="authStore.isAdmin" class="badge bg-primary">Admin</span>
          <button class="btn btn-outline-danger btn-sm" @click="handleLogout">
            <svg class="me-1" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="d-none d-sm-inline">Logout</span>
          </button>
        </div>
      </div>
    </header>

    <div class="d-flex flex-grow-1">
      <!-- Sidebar -->
      <aside 
        class="bg-white border-end shadow-sm"
        :class="{ 'd-none': !isSidebarOpen }"
        style="width: 260px; min-height: calc(100vh - 56px);"
      >
        <nav class="p-3">
          <NuxtLink 
            to="/dashboard" 
            class="d-flex align-items-center px-3 py-2 rounded mb-1 text-decoration-none"
            :class="isActive('/dashboard') ? 'bg-primary text-white' : 'text-dark hover-bg-light'"
          >
            <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="fw-medium">Dashboard</span>
          </NuxtLink>

          <NuxtLink 
            to="/documents" 
            class="d-flex align-items-center px-3 py-2 rounded mb-1 text-decoration-none"
            :class="isActive('/documents') ? 'bg-primary text-white' : 'text-dark hover-bg-light'"
          >
            <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="fw-medium">Dokumen</span>
          </NuxtLink>

          <NuxtLink 
            to="/approvals" 
            class="d-flex align-items-center px-3 py-2 rounded mb-1 text-decoration-none"
            :class="isActive('/approvals') ? 'bg-primary text-white' : 'text-dark hover-bg-light'"
          >
            <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="fw-medium">Approval</span>
          </NuxtLink>

          <NuxtLink 
            v-if="authStore.isAdmin"
            to="/users" 
            class="d-flex align-items-center px-3 py-2 rounded mb-1 text-decoration-none"
            :class="isActive('/users') ? 'bg-primary text-white' : 'text-dark hover-bg-light'"
          >
            <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="fw-medium">Users</span>
          </NuxtLink>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-grow-1 bg-light">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<style scoped>
.hover-bg-light:hover {
  background-color: #f8f9fa;
}
</style>
