<template>
  <div class="card glass-card" :class="{ 'dark-mode': isDark }">
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
const { isDark } = useTheme()
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.glass-card.dark-mode {
  background: rgba(31, 41, 55, 0.7);
  border: 1px solid rgba(75, 85, 99, 0.5);
  color: #f3f4f6;
}
</style>
