<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-lg border-0" style="max-width: 420px; width: 100%;">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <img src="/logo.png" alt="YPT Logo" class="mb-3" style="width: 64px; height: 64px;" />
          <h4 class="fw-bold text-primary">Daftar Akun Baru</h4>
          <p class="text-muted small">Buat akun untuk menggunakan sistem</p>
        </div>

        <form @submit.prevent="handleRegister">
          <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="form-control"
              placeholder="Masukkan nama lengkap"
              required
            />
          </div>

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
              placeholder="Minimal 8 karakter"
              required
              minlength="8"
            />
          </div>

          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="form-control"
              placeholder="Ulangi password"
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
            {{ loading ? 'Memproses...' : 'Daftar' }}
          </button>
        </form>

        <div class="text-center mt-4">
          <p class="text-muted small mb-0">
            Sudah punya akun?
            <NuxtLink to="/login" class="text-primary text-decoration-none fw-semibold">
              Masuk
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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref<string | null>(null)

const handleRegister = async () => {
  loading.value = true
  error.value = null

  if (form.password !== form.password_confirmation) {
    error.value = 'Password dan konfirmasi password tidak cocok.'
    loading.value = false
    return
  }

  const result = await authStore.register({
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
  })

  loading.value = false

  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.error || 'Pendaftaran gagal. Silakan coba lagi.'
  }
}

// Redirect if already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})
</script>
