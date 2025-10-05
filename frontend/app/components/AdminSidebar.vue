<template>
  <div
    v-show="isOpen"
    class="bg-telkom-white shadow h-screen w-64 transition-all duration-300 ease-in-out"
  >
    <nav class="mt-8">
      <div class="px-4 space-y-2">
        <NuxtLink
          to="/admin"
          class="flex items-center px-4 py-2 text-telkom-grey-dark hover:bg-telkom-grey rounded-md transition-colors duration-200"
          :class="{ 'bg-telkom-maroon text-telkom-white': $route.path === '/admin' }"
        >
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
          </svg>
          <span class="transition-opacity duration-200">Dashboard</span>
        </NuxtLink>
        <NuxtLink
          to="/admin/users"
          class="flex items-center px-4 py-2 text-telkom-grey-dark hover:bg-telkom-grey rounded-md transition-colors duration-200"
          :class="{ 'bg-telkom-maroon text-telkom-white': $route.path === '/admin/users' }"
        >
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <span class="transition-opacity duration-200">Manajemen User</span>
        </NuxtLink>
      </div>
    </nav>
  </div>
</template>

<script setup>
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true
  }
})

// Expose props for parent components if needed
defineExpose({
  isOpen: props.isOpen
})
</script>