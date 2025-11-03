<template>
  <div 
    class="relative"
    :class="[
      sizeClass,
      className
    ]"
  >
    <!-- Icon Container -->
    <div 
      class="relative rounded-xl border flex items-center justify-center transition-colors duration-150"
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
      class="absolute -top-2 -right-2 text-xs font-bold px-3 py-1 rounded-full border"
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
      return 'bg-blue-50 border-blue-200 text-blue-600'
    case 'purple':
      return 'bg-purple-50 border-purple-200 text-purple-600'
    case 'gold':
      return 'bg-yellow-50 border-yellow-200 text-yellow-600'
    case 'green':
      return 'bg-green-50 border-green-200 text-green-600'
    default:
      return 'bg-red-50 border-red-200 text-telkom-red'
  }
})

const badgeColorClass = computed(() => {
  switch (props.badgeColor) {
    case 'red':
      return 'bg-red-500 text-white border-red-600'
    case 'blue':
      return 'bg-blue-500 text-white border-blue-600'
    case 'green':
      return 'bg-green-500 text-white border-green-600'
    default:
      return 'bg-yellow-400 text-gray-900 border-yellow-500'
  }
})
</script>
