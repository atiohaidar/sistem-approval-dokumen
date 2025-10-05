<template>
  <div class="min-h-screen bg-telkom-white">
    <nav class="bg-telkom-maroon shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-telkom-white">
              Sistem Approval Dokumen
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-telkom-white">
              Selamat datang, {{ authStore.user?.name }}
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

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-telkom-grey rounded-lg p-8">
          <h2 class="text-2xl font-bold text-telkom-grey-dark mb-4">
            Dashboard
          </h2>
          <p class="text-telkom-grey">
            Selamat datang di Sistem Approval Dokumen Telkom University.
          </p>
          <div class="mt-6">
            <div class="bg-telkom-white p-4 rounded-lg shadow">
              <h3 class="text-lg font-semibold text-telkom-grey-dark mb-2">
                Informasi Akun
              </h3>
              <p><strong>Nama:</strong> {{ authStore.user?.name }}</p>
              <p><strong>Email:</strong> {{ authStore.user?.email }}</p>
              <p><strong>Role:</strong> {{ authStore.user?.role }}</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
}
</script>