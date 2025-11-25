<template>
  <div class="mini-timeline">
    <div class="flex items-center space-x-1">
      <div 
        v-for="(level, index) in levels" 
        :key="index"
        class="flex items-center"
      >
        <!-- Timeline Node -->
        <div 
          class="timeline-node"
          :class="getNodeClass(level.status)"
          :title="`Level ${index + 1}: ${getStatusText(level.status)}`"
        >
          <svg v-if="level.status === 'completed'" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          <svg v-else-if="level.status === 'rejected'" class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
          <div v-else-if="level.status === 'in_progress'" class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></div>
          <div v-else class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
        </div>
        
        <!-- Connector Line -->
        <div 
          v-if="index < levels.length - 1"
          class="timeline-connector"
          :class="getConnectorClass(level.status)"
        ></div>
      </div>
    </div>
    
    <!-- Status Text -->
    <div class="mt-1">
      <span :class="getStatusTextClass()" class="text-xs font-medium">
        {{ getOverallStatusText() }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
interface MiniLevel {
  status: 'completed' | 'in_progress' | 'rejected' | 'pending'
}

interface Props {
  documentStatus: string
  currentLevel: number
  totalLevels: number
  isDark?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isDark: false
})

const { isDark } = useTheme()

const levels = computed(() => {
  const result: MiniLevel[] = []
  
  for (let i = 1; i <= props.totalLevels; i++) {
    let status: MiniLevel['status'] = 'pending'
    
    if (i < props.currentLevel) {
      status = 'completed'
    } else if (i === props.currentLevel) {
      if (props.documentStatus === 'completed') {
        status = 'completed'
      } else if (props.documentStatus === 'rejected') {
        status = 'rejected'
      } else if (props.documentStatus === 'pending_approval') {
        status = 'in_progress'
      }
    }
    
    result.push({ status })
  }
  
  return result
})

const getNodeClass = (status: string) => {
  const baseClass = 'w-5 h-5 rounded-full flex items-center justify-center text-white transition-all duration-200'
  
  switch (status) {
    case 'completed':
      return `${baseClass} bg-green-500 shadow-sm`
    case 'in_progress':
      return `${baseClass} ${isDark.value ? 'bg-gradient-to-br from-red-500 to-orange-500' : 'bg-gradient-to-br from-red-500 to-orange-500'} shadow-sm`
    case 'rejected':
      return `${baseClass} bg-red-500 shadow-sm`
    default:
      return `${baseClass} ${isDark.value ? 'bg-gray-600' : 'bg-gray-300'}`
  }
}

const getConnectorClass = (status: string) => {
  const baseClass = 'w-3 h-0.5 mx-0.5'
  
  if (status === 'completed') {
    return `${baseClass} bg-green-500`
  }
  if (status === 'rejected') {
    return `${baseClass} bg-red-500`
  }
  return `${baseClass} ${isDark.value ? 'bg-gray-600' : 'bg-gray-300'}`
}

const getStatusTextClass = () => {
  if (props.documentStatus === 'completed') {
    return isDark.value ? 'text-green-300' : 'text-green-600'
  }
  if (props.documentStatus === 'rejected') {
    return isDark.value ? 'text-red-300' : 'text-red-600'
  }
  if (props.documentStatus === 'pending_approval') {
    return isDark.value ? 'text-orange-300' : 'text-orange-600'
  }
  return isDark.value ? 'text-gray-400' : 'text-gray-500'
}

const getOverallStatusText = () => {
  const statusMap = {
    draft: 'Draft',
    pending_approval: `Level ${props.currentLevel}/${props.totalLevels}`,
    completed: 'Selesai',
    rejected: 'Ditolak'
  }
  return statusMap[props.documentStatus as keyof typeof statusMap] || props.documentStatus
}

const getStatusText = (status: string) => {
  const statusMap = {
    completed: 'Selesai',
    in_progress: 'Sedang Berlangsung',
    rejected: 'Ditolak',
    pending: 'Menunggu'
  }
  return statusMap[status as keyof typeof statusMap] || status
}
</script>

<style scoped>
.mini-timeline {
  @apply min-w-0;
}

.timeline-node {
  position: relative;
}

.timeline-node:hover {
  transform: scale(1.1);
}

.timeline-connector {
  @apply transition-all duration-200;
}
</style>
