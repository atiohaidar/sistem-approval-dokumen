<template>
  <div class="min-h-screen bg-telkom-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-telkom-grey-dark">
          Daftar Akun Baru
        </h2>
        <p class="mt-2 text-center text-sm text-telkom-grey">
          Sistem Approval Dokumen Telkom University
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="name" class="sr-only">Nama</label>
            <input
              id="name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-telkom-grey placeholder-telkom-grey text-telkom-grey-dark rounded-t-md focus:outline-none focus:ring-telkom-red focus:border-telkom-red focus:z-10 sm:text-sm"
              placeholder="Nama Lengkap"
              v-model="form.name"
            />
          </div>
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-telkom-grey placeholder-telkom-grey text-telkom-grey-dark focus:outline-none focus:ring-telkom-red focus:border-telkom-red focus:z-10 sm:text-sm"
              placeholder="Email"
              v-model="form.email"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-telkom-grey placeholder-telkom-grey text-telkom-grey-dark focus:outline-none focus:ring-telkom-red focus:border-telkom-red focus:z-10 sm:text-sm"
              placeholder="Password"
              v-model="form.password"
            />
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Konfirmasi Password</label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-telkom-grey placeholder-telkom-grey text-telkom-grey-dark rounded-b-md focus:outline-none focus:ring-telkom-red focus:border-telkom-red focus:z-10 sm:text-sm"
              placeholder="Konfirmasi Password"
              v-model="form.password_confirmation"
            />
          </div>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-telkom-white bg-telkom-maroon hover:bg-telkom-red focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-telkom-red disabled:opacity-50"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-telkom-white"></span>
            <span v-else>Daftar</span>
          </button>
        </div>

        <div class="text-center">
          <NuxtLink to="/login" class="text-telkom-red hover:text-telkom-maroon">
            Sudah punya akun? Masuk di sini
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
  loading.value = true
  error.value = ''

  try {
    await authStore.register(form.value)
    await navigateTo('/')
  } catch (err) {
    error.value = err.data?.message || 'Registrasi gagal. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>