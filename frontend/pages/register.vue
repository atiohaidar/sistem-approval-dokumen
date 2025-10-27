<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-telkom-red to-telkom-red-dark">
    <div class="max-w-md w-full mx-4">
      <div class="card">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-telkom-red mb-2">Daftar Akun</h1>
          <p class="text-gray-600">Sistem Approval Dokumen</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label class="form-label">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              placeholder="Nama Lengkap"
              required
            />
          </div>

          <div>
            <label class="form-label">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="form-input"
              placeholder="nama@telkom.co.id"
              required
            />
          </div>

          <div>
            <label class="form-label">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="form-input"
              placeholder="Minimal 8 karakter"
              required
              minlength="8"
            />
          </div>

          <div>
            <label class="form-label">Konfirmasi Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="form-input"
              placeholder="Ulangi password"
              required
            />
          </div>

          <div v-if="error" class="p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ error }}
          </div>

          <button
            type="submit"
            class="w-full btn btn-primary"
            :disabled="loading"
          >
            {{ loading ? 'Loading...' : 'Daftar' }}
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
          Sudah punya akun?
          <NuxtLink to="/login" class="text-telkom-red hover:text-telkom-red-dark font-medium">
            Login di sini
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false,
})

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref<string | null>(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  if (form.password !== form.password_confirmation) {
    error.value = 'Password tidak cocok'
    loading.value = false
    return
  }

  const result = await authStore.register(form)

  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.error || 'Registrasi gagal. Silakan coba lagi.'
  }

  loading.value = false
}
</script>
