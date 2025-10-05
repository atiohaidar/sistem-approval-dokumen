<template>
  <div class="h-screen bg-telkom-white flex flex-col">
    <!-- Navigation -->
    <nav class="bg-telkom-white border-b border-telkom-grey shadow flex-shrink-0">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <!-- Hamburger Menu Button -->
            <button
              @click="toggleSidebar"
              class="mr-4 p-2 rounded-md text-telkom-grey-dark hover:bg-telkom-grey transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
            </button>
            <h1 class="text-xl font-bold text-telkom-grey-dark">
              Sistem Approval Dokumen - Admin
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-telkom-grey-dark">
              {{ authStore.user?.name }}
            </span>
            <button
              @click="handleLogout"
              class="bg-telkom-red hover:bg-red-700 text-telkom-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
      <AdminSidebar :is-open="isSidebarOpen" />

      <!-- Main Content -->
      <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-7xl mx-auto">
          <h2 class="text-3xl font-bold text-telkom-grey-dark mb-8">
            Dashboard Admin
          </h2>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-telkom-white p-6 rounded-lg shadow border border-telkom-grey">
              <div class="flex items-center">
                <div class="p-2 bg-telkom-maroon rounded-md">
                  <svg class="w-6 h-6 text-telkom-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-telkom-grey-dark">Total Users</p>
                  <p class="text-2xl font-semibold text-telkom-grey-dark">{{ userStore.users.length }}</p>
                </div>
              </div>
            </div>

            <div class="bg-telkom-white p-6 rounded-lg shadow border border-telkom-grey">
              <div class="flex items-center">
                <div class="p-2 bg-telkom-red rounded-md">
                  <svg class="w-6 h-6 text-telkom-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-telkom-grey-dark">Active Users</p>
                  <p class="text-2xl font-semibold text-telkom-grey-dark">{{ activeUsersCount }}</p>
                </div>
              </div>
            </div>

            <div class="bg-telkom-white p-6 rounded-lg shadow border border-telkom-grey">
              <div class="flex items-center">
                <div class="p-2 bg-telkom-grey rounded-md">
                  <svg class="w-6 h-6 text-telkom-grey-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-telkom-grey-dark">Admin Users</p>
                  <p class="text-2xl font-semibold text-telkom-grey-dark">{{ adminUsersCount }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Users Table -->
          <div class="bg-telkom-white shadow rounded-lg overflow-hidden border border-telkom-grey">
            <div class="px-6 py-4 border-b border-telkom-grey">
              <h3 class="text-lg font-medium text-telkom-grey-dark">Recent Users</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-telkom-grey">
                <thead class="bg-telkom-grey">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                      Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                      Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                      Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                      Created
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-telkom-white divide-y divide-telkom-grey">
                  <tr v-for="user in recentUsers" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-telkom-grey-dark">
                      {{ user.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-telkom-grey-dark">
                      {{ user.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="user.role === 'admin' ? 'bg-telkom-red text-telkom-white' : 'bg-telkom-grey text-telkom-grey-dark'"
                      >
                        {{ user.role }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-telkom-grey-dark">
                      {{ formatDate(user.created_at) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
import { useUserStore } from '~/stores/user'

definePageMeta({
  middleware: 'admin'
})

const authStore = useAuthStore()
const userStore = useUserStore()

// Sidebar state
const isSidebarOpen = ref(true)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

// Computed properties for stats
const activeUsersCount = computed(() => {
  return userStore.users.length // Assuming all users are active for now
})

const adminUsersCount = computed(() => {
  return userStore.users.filter(user => user.role === 'admin').length
})

const recentUsers = computed(() => {
  return userStore.users.slice(0, 5) // Show last 5 users
})

// Fetch users on page load
onMounted(async () => {
  try {
    await userStore.fetchUsers()
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
})

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID')
}
</script>