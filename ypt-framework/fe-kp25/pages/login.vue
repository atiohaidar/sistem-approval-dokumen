<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0" style="max-width: 420px; width: 100%;">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <img src="/logo.png" alt="YPT Logo" class="mb-3" style="width: 64px; height: 64px;" />
          <h4 class="fw-bold text-primary">Sistem Approval Dokumen</h4>
          <p class="text-muted small">Masuk ke akun Anda</p>
        </div>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-control"
              placeholder="Masukkan email"
              required
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="form-control"
              placeholder="Masukkan password"
              required
            />
          </div>

          <div v-if="error" class="alert alert-danger small py-2">
            {{ error }}
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 py-2"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>

        <div class="text-center mt-4">
          <p class="text-muted small mb-0">
            Belum punya akun?
            <NuxtLink to="/register" class="text-primary text-decoration-none fw-semibold">
              Daftar Sekarang
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
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

const handleLogin = async () => {
  loading.value = true
  error.value = null

  const result = await authStore.login({
    email: form.email,
    password: form.password,
  })

  loading.value = false

  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.error || 'Login gagal. Silakan coba lagi.'
  }
}

// Redirect if already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})
</script>
