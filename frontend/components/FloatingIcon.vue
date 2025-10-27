<template>
  <div 
    class="relative animate-float"
    :class="[
      sizeClass,
      delayClass,
      className
    ]"
  >
    <!-- Glow Background -->
    <div 
      v-if="glow"
      class="absolute inset-0 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition"
      :class="glowColorClass"
    ></div>

    <!-- Icon Container -->
    <div 
      class="relative backdrop-blur-md rounded-2xl border flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110"
      :class="[
        containerClass,
        iconColorClass
      ]"
    >
      <slot>
        <svg 
          v-if="icon === 'check'"
          class="w-8 h-8" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M5 13l4 4L19 7" 
          />
        </svg>
        <svg 
          v-else-if="icon === 'lock'"
          class="w-8 h-8" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" 
          />
        </svg>
        <svg 
          v-else-if="icon === 'star'"
          class="w-8 h-8" 
          fill="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" 
          />
        </svg>
      </slot>
    </div>

    <!-- Badge (optional) -->
    <div 
      v-if="badge"
      class="absolute -top-2 -right-2 text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse"
      :class="badgeColorClass"
    >
      {{ badge }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  icon?: 'check' | 'lock' | 'star' | 'custom'
  color?: 'telkom' | 'blue' | 'purple' | 'gold' | 'green'
  size?: 'sm' | 'md' | 'lg'
  glow?: boolean
  badge?: string
  badgeColor?: 'yellow' | 'red' | 'blue' | 'green'
  delay?: '0' | '500' | '1000'
  className?: string
}

const props = withDefaults(defineProps<Props>(), {
  icon: 'check',
  color: 'telkom',
  size: 'md',
  glow: true,
  badge: '',
  badgeColor: 'yellow',
  delay: '0',
  className: ''
})

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-16 h-16'
    case 'lg':
      return 'w-24 h-24'
    default:
      return 'w-20 h-20'
  }
})

const containerClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-16 h-16'
    case 'lg':
      return 'w-24 h-24'
    default:
      return 'w-20 h-20'
  }
})

const iconColorClass = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'bg-blue-500/20 border-blue-500/30 text-blue-400'
    case 'purple':
      return 'bg-purple-500/20 border-purple-500/30 text-purple-400'
    case 'gold':
      return 'bg-yellow-400/20 border-yellow-400/30 text-yellow-400'
    case 'green':
      return 'bg-green-500/20 border-green-500/30 text-green-400'
    default:
      return 'bg-telkom-red/20 border-telkom-red/30 text-telkom-red'
  }
})

const glowColorClass = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'bg-blue-500/30'
    case 'purple':
      return 'bg-purple-500/30'
    case 'gold':
      return 'bg-yellow-400/30'
    case 'green':
      return 'bg-green-500/30'
    default:
      return 'bg-telkom-red/30'
  }
})

const badgeColorClass = computed(() => {
  switch (props.badgeColor) {
    case 'red':
      return 'bg-red-500 text-white'
    case 'blue':
      return 'bg-blue-500 text-white'
    case 'green':
      return 'bg-green-500 text-white'
    default:
      return 'bg-yellow-400 text-gray-900'
  }
})

const delayClass = computed(() => {
  return props.delay === '500' ? 'delay-500' : props.delay === '1000' ? 'delay-1000' : ''
})
</script>
