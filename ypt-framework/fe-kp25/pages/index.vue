<template>
  <div
    class="min-vh-100 d-flex justify-content-center align-items-center text-white animated-gradient"
  >
    <div class="container text-center">
      <img src="/logo.png" alt="YPT Logo" class="mb-4" style="width: 80px; height: 80px;" />
      <h1 class="display-4 fw-semibold">Sistem Approval Dokumen</h1>
      <h2 class="fw-light">Yayasan Pendidikan Telkom</h2>
      <p class="lead mt-3">Multi-level document approval dengan digital signature & QR code verification</p>
      <div class="d-flex justify-content-center align-items-center gap-2 mt-4">
        <NuxtLink to="/login" class="btn btn-primary btn-lg">
          Masuk
        </NuxtLink>
        <NuxtLink to="/register" class="btn btn-outline-light btn-lg">
          Daftar
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animated-gradient {
  background: linear-gradient(135deg, #4F5686, #2a2e4a, #4F5686);
  background-size: 400% 400%;
  animation: gradientMove 10s ease infinite;
}

@keyframes gradientMove {
  0% {
    background-position: 0 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0 50%;
  }
}
</style>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

const authStore = useAuthStore()
const router = useRouter()

// Redirect if already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})
</script>
