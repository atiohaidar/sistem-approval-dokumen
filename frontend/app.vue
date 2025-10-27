<template>
  <div>
    <div v-if="isAuthenticated">
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </div>
    <div v-else>
      <NuxtPage />
    </div>
    <!-- TanStack Query Devtools - only in development -->
    <ClientOnly>
      <Suspense v-if="isDev">
        <VueQueryDevtools />
      </Suspense>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { VueQueryDevtools } from '@tanstack/vue-query-devtools'

const authStore = useAuthStore()
const { isAuthenticated } = storeToRefs(authStore)
const isDev = process.dev

onMounted(() => {
  authStore.initializeFromCookie()
})
</script>
