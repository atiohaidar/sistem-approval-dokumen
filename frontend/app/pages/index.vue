<template>
  <div class="min-h-screen bg-telkom-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-telkom-grey-dark">
          Selamat Datang
        </h2>
        <p class="mt-2 text-center text-sm text-telkom-grey">
          Sistem Approval Dokumen Telkom University
        </p>
      </div>
      <div class="text-center">
        <p class="text-telkom-grey-dark mb-4">
          Anda login sebagai: <strong>{{ authStore.user?.name }}</strong>
        </p>
        <p class="text-telkom-grey mb-6">
          Role: <span class="font-semibold" :class="authStore.userRole === 'admin' ? 'text-telkom-red' : 'text-telkom-maroon'">
            {{ authStore.userRole }}
          </span>
        </p>
        <div v-if="authStore.userRole === 'admin'" class="space-y-4">
          <NuxtLink
            to="/admin"
            class="block w-full bg-telkom-maroon hover:bg-telkom-red text-telkom-white font-bold py-2 px-4 rounded"
          >
            Admin Dashboard
          </NuxtLink>
        </div>
        <button
          @click="handleLogout"
          class="mt-4 bg-telkom-grey hover:bg-gray-300 text-telkom-grey-dark font-bold py-2 px-4 rounded"
        >
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  middleware: 'auth'
})

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
}
</script>