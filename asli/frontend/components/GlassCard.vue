<template>
  <div 
    class="glass-card"
    :class="[
      variantClass,
      className
    ]"
  >
    <!-- Content Slot -->
    <slot />
  </div>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'default' | 'light' | 'dark' | 'strong'
  glow?: boolean
  glowColor?: 'telkom' | 'blue' | 'purple' | 'gold'
  hoverEffect?: boolean
  decorativeCorner?: boolean
  className?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  glow: false,
  glowColor: 'telkom',
  hoverEffect: true,
  decorativeCorner: false,
  className: ''
})

const variantClass = computed(() => {
  switch (props.variant) {
    case 'light':
      return 'glass-light'
    case 'dark':
      return 'glass-dark'
    case 'strong':
      return 'glass-strong'
    default:
      return 'glass'
  }
})
</script>

<style scoped>
.glass-card {
  transition: box-shadow 0.2s ease;
}
</style>
