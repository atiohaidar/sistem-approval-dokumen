<template>
  <component
    :is="resolvedTag"
    :type="tag === 'button' ? type : undefined"
    class="font-semibold transition-colors duration-150"
    :class="[
      sizeClass,
      variantClass,
      fullWidth && 'w-full',
      className
    ]"
    @click="handleClick"
  >
    <!-- Content -->
    <span class="flex items-center justify-center gap-2">
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
      return 'bg-blue-600 text-white hover:bg-blue-700'
    case 'purple':
      return 'bg-purple-600 text-white hover:bg-purple-700'
    case 'outline':
      return 'bg-transparent border-2 border-white text-white hover:bg-white hover:text-telkom-red'
    case 'glass':
      return 'bg-white border border-gray-300 text-gray-900 hover:bg-gray-50'
    case 'white':
      return 'bg-white text-telkom-red hover:bg-gray-50'
    default:
      return 'bg-telkom-red text-white hover:bg-telkom-red-dark'
  }
})
</script>

<style scoped>
</style>
