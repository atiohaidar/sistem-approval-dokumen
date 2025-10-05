<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtPage />
  </div>
</template>

<script setup>
const authStore = useAuthStore()

// Initialize auth on app start
onMounted(() => {
  authStore.initializeAuth()
})
</script>
