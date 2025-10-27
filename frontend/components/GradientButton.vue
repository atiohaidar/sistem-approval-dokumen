<template>
  <component
    :is="resolvedTag"
    :type="tag === 'button' ? type : undefined"
    class="relative overflow-hidden font-bold transition-all duration-300 group"
    :class="[
      sizeClass,
      variantClass,
      fullWidth && 'w-full',
      className
    ]"
    @click="handleClick"
  >
    <!-- Background Gradient Animation -->
    <div 
      v-if="animatedGradient"
      class="absolute inset-0 bg-gradient-to-r translate-x-full group-hover:translate-x-0 transition-transform duration-300 pointer-events-none"
      :class="hoverGradientClass"
    ></div>

    <!-- Content -->
    <span class="relative z-10 flex items-center justify-center gap-2">
      <!-- Icon Left -->
      <slot name="icon-left" />
      
      <!-- Text -->
      <slot />
      
      <!-- Icon Right -->
      <slot name="icon-right">
        <svg 
          v-if="showArrow"
          class="w-5 h-5" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M13 7l5 5m0 0l-5 5m5-5H6" 
          />
        </svg>
      </slot>
    </span>

    <!-- Glow Effect on Hover -->
    <div 
      v-if="glow"
      class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
      :class="glowClass"
    ></div>
  </component>
</template>

<script setup lang="ts">
import { resolveComponent, computed } from 'vue'
interface Props {
  variant?: 'telkom' | 'blue' | 'purple' | 'outline' | 'glass' | 'white'
  size?: 'sm' | 'md' | 'lg' | 'xl'
  tag?: 'button' | 'a' | 'NuxtLink'
  type?: 'button' | 'submit' | 'reset'
  fullWidth?: boolean
  showArrow?: boolean
  animatedGradient?: boolean
  glow?: boolean
  className?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'telkom',
  size: 'md',
  tag: 'button',
  type: 'button',
  fullWidth: false,
  showArrow: false,
  animatedGradient: true,
  glow: true,
  className: ''
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

const handleClick = (event: MouseEvent) => {
  emit('click', event)
}

// Ensure dynamic component resolves NuxtLink properly
const resolvedTag = computed(() => {
  return props.tag === 'NuxtLink' ? (resolveComponent('NuxtLink') as any) : props.tag
})

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-4 py-2 text-sm rounded-lg'
    case 'lg':
      return 'px-8 py-4 text-lg rounded-xl'
    case 'xl':
      return 'px-10 py-5 text-xl rounded-2xl'
    default:
      return 'px-6 py-3 rounded-xl'
  }
})

const variantClass = computed(() => {
  switch (props.variant) {
    case 'blue':
      return 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-2xl shadow-blue-600/50 hover:shadow-blue-600 hover:scale-105'
    case 'purple':
      return 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-2xl shadow-purple-600/50 hover:shadow-purple-600 hover:scale-105'
    case 'outline':
      return 'bg-transparent border-2 border-white text-white hover:bg-white hover:text-telkom-red'
    case 'glass':
      return 'glass text-white hover:glass-light'
    case 'white':
      return 'bg-white text-telkom-red hover:bg-yellow-400 hover:text-white shadow-lg hover:shadow-yellow-400/50'
    default:
      return 'bg-gradient-to-r from-telkom-red to-orange-600 text-white shadow-2xl shadow-telkom-red/50 hover:shadow-telkom-red hover:scale-105'
  }
})

const hoverGradientClass = computed(() => {
  switch (props.variant) {
    case 'blue':
      return 'from-cyan-600 to-blue-700'
    case 'purple':
      return 'from-pink-600 to-purple-700'
    case 'white':
      return 'from-yellow-400 to-yellow-500'
    default:
      return 'from-orange-600 to-red-700'
  }
})

const glowClass = computed(() => {
  switch (props.variant) {
    case 'blue':
      return 'shadow-[0_0_40px_rgba(59,130,246,0.6)]'
    case 'purple':
      return 'shadow-[0_0_40px_rgba(147,51,234,0.6)]'
    default:
      return 'shadow-[0_0_40px_rgba(239,49,36,0.6)]'
  }
})
</script>

<style scoped>
</style>
