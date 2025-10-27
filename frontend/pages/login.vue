<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Image/Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/90 via-blue-700/90 to-indigo-800/90"></div>
      <div class="relative z-10 flex flex-col justify-center items-center text-white p-12">
        <div class="mb-8">
          <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-2xl animate-float">
            <img src="/logo.png" alt="Logo YPT" class="w-24 h-24 object-contain" />
          </div>
        </div>
        <h1 class="text-4xl font-bold mb-4 text-center">Sistem Approval Dokumen</h1>
        <div class="w-24 h-1 bg-gradient-to-r from-orange-400 to-orange-500 mb-6"></div>
        <p class="text-xl text-center text-white/90 max-w-md">
          Solusi digital untuk manajemen approval dokumen dengan sistem multi-tingkat yang aman dan efisien
        </p>
        <div class="mt-12 grid grid-cols-3 gap-8 text-center">
          <div class="animate-fade-in-up">
            <div class="text-3xl font-bold text-orange-400">100+</div>
            <div class="text-sm text-white/80">Pengguna</div>
          </div>
          <div class="animate-fade-in-up delay-100">
            <div class="text-3xl font-bold text-orange-400">1000+</div>
            <div class="text-sm text-white/80">Dokumen</div>
          </div>
          <div class="animate-fade-in-up delay-200">
            <div class="text-3xl font-bold text-orange-400">99%</div>
            <div class="text-sm text-white/80">Uptime</div>
          </div>
        </div>
      </div>
      <!-- Decorative circles -->
      <div class="absolute top-10 right-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-10 left-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gradient-to-br from-blue-50 via-white to-orange-50">
      <div class="w-full max-w-md">
        <!-- Logo for mobile -->
        <div class="lg:hidden text-center mb-8">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            <img src="/logo.png" alt="Logo YPT" class="w-14 h-14 object-contain" />
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Sistem Approval</h2>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-2xl shadow-xl p-8">
          <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang!</h2>
            <p class="text-gray-600">Silakan masuk ke akun Anda</p>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
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
                placeholder="••••••••"
                required
              />
            </div>

            <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
              {{ error }}
            </div>

            <button
              type="submit"
              class="w-full gradient-button"
              :disabled="loading"
            >
              {{ loading ? 'Loading...' : 'Login' }}
            </button>
          </form>

          <div class="mt-6 text-center text-sm text-gray-600">
            Belum punya akun?
            <NuxtLink to="/register" class="text-blue-600 hover:text-blue-700 font-medium">
              Daftar di sini
            </NuxtLink>
          </div>

          <div class="mt-6 pt-6 border-t border-gray-200 text-center">
            <NuxtLink to="/" class="text-sm text-gray-600 hover:text-blue-600 transition">
              ← Kembali ke Beranda
            </NuxtLink>
          </div>
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
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref<string | null>(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  const result = await authStore.login(form)

  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.error || 'Login gagal. Silakan coba lagi.'
  }

  loading.value = false
}
</script>
