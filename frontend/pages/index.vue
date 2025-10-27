<template>
  <div>
    <NuxtLayout>
      <div class="text-center">
        <h1 class="text-4xl font-bold text-telkom-red mb-4">Selamat Datang</h1>
        <p class="text-gray-600 mb-8">Sistem Approval Dokumen Telkom Indonesia</p>
        <NuxtLink to="/login" class="btn btn-primary">
          Login
        </NuxtLink>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

// Redirect to dashboard if authenticated
const authToken = useCookie('auth_token')
if (authToken.value) {
  navigateTo('/dashboard')
}
</script>
