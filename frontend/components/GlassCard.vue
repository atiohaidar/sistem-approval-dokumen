<template>
  <div 
    class="glass-card relative overflow-hidden"
    :class="[
      variantClass,
      hoverEffect && 'hover:scale-105',
      className
    ]"
  >
    <!-- Glow Effect (optional) -->
    <div 
      v-if="glow"
      class="absolute inset-0 opacity-0 group-hover:opacity-75 transition-opacity duration-500 blur-xl"
      :class="glowColorClass"
    ></div>

    <!-- Content Slot -->
    <div class="relative z-10">
      <slot />
    </div>

    <!-- Decorative Corner (optional) -->
    <div 
      v-if="decorativeCorner"
      class="absolute -top-2 -right-2 w-20 h-20 bg-gradient-to-br from-yellow-400/20 to-transparent rounded-full blur-xl"
    ></div>
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

const glowColorClass = computed(() => {
  switch (props.glowColor) {
    case 'blue':
      return 'bg-gradient-to-br from-blue-500/50 to-cyan-500/50'
    case 'purple':
      return 'bg-gradient-to-br from-purple-500/50 to-pink-500/50'
    case 'gold':
      return 'bg-gradient-to-br from-yellow-400/50 to-orange-500/50'
    default:
      return 'bg-gradient-to-br from-telkom-red/50 to-orange-600/50'
  }
})
</script>

<style scoped>
.glass-card {
  transition: all 0.3s ease;
}
</style>
