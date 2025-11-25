<template>
  <div class="approval-timeline">
    <div class="timeline-container">
      <div 
        v-for="(level, index) in approvalLevels" 
        :key="index"
        class="timeline-item"
        :class="getTimelineItemClass(level.status)"
      >
        <!-- Timeline Line -->
        <div class="timeline-line" v-if="index < approvalLevels.length - 1">
          <div 
            class="timeline-progress"
            :class="getTimelineProgressClass(level.status, approvalLevels[index + 1]?.status)"
          ></div>
        </div>

        <!-- Timeline Node -->
        <div class="timeline-node" :class="getTimelineNodeClass(level.status)">
          <div class="timeline-icon">
            <!-- Completed Icon -->
            <svg v-if="level.status === 'completed'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <!-- In Progress Icon -->
            <svg v-else-if="level.status === 'in_progress'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            <!-- Rejected Icon -->
            <svg v-else-if="level.status === 'rejected'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <!-- Pending Icon -->
            <div v-else class="w-3 h-3 rounded-full bg-current opacity-50"></div>
          </div>
        </div>

        <!-- Timeline Content -->
        <div class="timeline-content">
          <div class="timeline-header">
            <h3 class="timeline-title">Level {{ index + 1 }}</h3>
            <span class="timeline-badge" :class="getTimelineBadgeClass(level.status)">
              {{ getStatusText(level.status) }}
            </span>
          </div>

          <!-- Approvers List -->
          <div class="timeline-approvers">
            <div 
              v-for="approver in level.approvers" 
              :key="approver.id"
              class="approver-item group relative"
              :class="getApproverItemClass(approver.status)"
              :title="getApproverTooltip(approver)"
            >
              <div class="approver-avatar">
                <div class="avatar-circle" :class="getAvatarClass(approver.status)">
                  {{ getInitials(approver.name) }}
                </div>
                <div class="approver-status-indicator" :class="getStatusIndicatorClass(approver.status)">
                  <svg v-if="approver.status === 'approved'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else-if="approver.status === 'rejected'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                  <div v-else-if="approver.status === 'pending'" class="w-2 h-2 rounded-full bg-current animate-pulse"></div>
                </div>
              </div>
              <div class="approver-info flex-1">
                <div class="flex items-center justify-between">
                  <div class="approver-name">{{ approver.name }}</div>
                  <div v-if="approver.timestamp" class="approver-timestamp">
                    {{ formatApproverTimestamp(approver.timestamp) }}
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <div class="approver-status" :class="getApproverStatusClass(approver.status)">
                    {{ getApproverStatusText(approver.status) }}
                  </div>
                  <div v-if="approver.notes" class="approver-notes-indicator">
                    <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Tooltip for notes -->
              <div 
                v-if="approver.notes && (approver.status === 'approved' || approver.status === 'rejected')"
                class="approver-tooltip"
              >
                <div class="tooltip-content">
                  <div class="tooltip-header">
                    <span class="font-semibold">{{ approver.name }}</span>
                    <span class="text-xs opacity-75">{{ formatApproverTimestamp(approver.timestamp) }}</span>
                  </div>
                  <div class="tooltip-message">{{ approver.notes }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Timestamp -->
          <div v-if="level.timestamp" class="timeline-timestamp">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ formatTimestamp(level.timestamp) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Approver {
  id: number
  name: string
  status: 'approved' | 'rejected' | 'pending'
  timestamp?: string
  notes?: string
}

interface ApprovalLevel {
  status: 'completed' | 'in_progress' | 'rejected' | 'pending'
  approvers: Approver[]
  timestamp?: string
}

interface Props {
  approvalLevels: ApprovalLevel[]
  isDark?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isDark: false
})

const { isDark } = useTheme()

const getTimelineItemClass = (status: string) => {
  return {
    'timeline-item-completed': status === 'completed',
    'timeline-item-in-progress': status === 'in_progress',
    'timeline-item-rejected': status === 'rejected',
    'timeline-item-pending': status === 'pending'
  }
}

const getTimelineProgressClass = (currentStatus: string, nextStatus?: string) => {
  if (currentStatus === 'completed') {
    return isDark.value ? 'bg-gradient-to-b from-green-400 to-green-500' : 'bg-gradient-to-b from-green-500 to-green-600'
  }
  if (currentStatus === 'rejected') {
    return isDark.value ? 'bg-gradient-to-b from-red-400 to-red-500' : 'bg-gradient-to-b from-red-500 to-red-600'
  }
  return isDark.value ? 'bg-gray-600' : 'bg-gray-300'
}

const getTimelineNodeClass = (status: string) => {
  const baseClass = 'timeline-node-base'
  if (status === 'completed') {
    return `${baseClass} ${isDark.value ? 'bg-green-500 text-white shadow-green-500/50' : 'bg-green-500 text-white shadow-green-500/30'}`
  }
  if (status === 'in_progress') {
    return `${baseClass} ${isDark.value ? 'bg-gradient-to-br from-red-500 to-orange-500 text-white shadow-red-500/50 animate-pulse' : 'bg-gradient-to-br from-red-500 to-orange-500 text-white shadow-red-500/30 animate-pulse'}`
  }
  if (status === 'rejected') {
    return `${baseClass} ${isDark.value ? 'bg-red-500 text-white shadow-red-500/50' : 'bg-red-500 text-white shadow-red-500/30'}`
  }
  return `${baseClass} ${isDark.value ? 'bg-gray-600 text-gray-300' : 'bg-gray-300 text-gray-600'}`
}

const getTimelineBadgeClass = (status: string) => {
  if (status === 'completed') {
    return isDark.value ? 'bg-green-500/20 text-green-300 border-green-500/30' : 'bg-green-100 text-green-700 border-green-200'
  }
  if (status === 'in_progress') {
    return isDark.value ? 'bg-orange-500/20 text-orange-300 border-orange-500/30' : 'bg-orange-100 text-orange-700 border-orange-200'
  }
  if (status === 'rejected') {
    return isDark.value ? 'bg-red-500/20 text-red-300 border-red-500/30' : 'bg-red-100 text-red-700 border-red-200'
  }
  return isDark.value ? 'bg-gray-600/20 text-gray-400 border-gray-600/30' : 'bg-gray-100 text-gray-600 border-gray-200'
}

const getApproverItemClass = (status: string) => {
  if (status === 'approved') {
    return isDark.value ? 'bg-green-500/10 border-green-500/20' : 'bg-green-50 border-green-100'
  }
  if (status === 'rejected') {
    return isDark.value ? 'bg-red-500/10 border-red-500/20' : 'bg-red-50 border-red-100'
  }
  return isDark.value ? 'bg-gray-800/50 border-gray-700/50' : 'bg-gray-50 border-gray-100'
}

const getAvatarClass = (status: string) => {
  if (status === 'approved') {
    return isDark.value ? 'bg-green-600 text-white' : 'bg-green-500 text-white'
  }
  if (status === 'rejected') {
    return isDark.value ? 'bg-red-600 text-white' : 'bg-red-500 text-white'
  }
  return isDark.value ? 'bg-gray-600 text-gray-200' : 'bg-gray-400 text-white'
}

const getStatusIndicatorClass = (status: string) => {
  if (status === 'approved') {
    return 'bg-green-500 text-white'
  }
  if (status === 'rejected') {
    return 'bg-red-500 text-white'
  }
  return isDark.value ? 'bg-orange-500 text-white' : 'bg-orange-500 text-white'
}

const getApproverStatusClass = (status: string) => {
  if (status === 'approved') {
    return isDark.value ? 'text-green-300' : 'text-green-600'
  }
  if (status === 'rejected') {
    return isDark.value ? 'text-red-300' : 'text-red-600'
  }
  return isDark.value ? 'text-orange-300' : 'text-orange-600'
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

const getApproverStatusText = (status: string) => {
  const statusMap = {
    approved: 'Disetujui',
    rejected: 'Ditolak',
    pending: 'Menunggu'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatTimestamp = (timestamp: string) => {
  return new Date(timestamp).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatApproverTimestamp = (timestamp?: string) => {
  if (!timestamp) return ''
  return new Date(timestamp).toLocaleDateString('id-ID', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getApproverTooltip = (approver: Approver) => {
  let tooltip = `${approver.name} - ${getApproverStatusText(approver.status)}`
  
  if (approver.timestamp) {
    tooltip += `\nWaktu: ${formatTimestamp(approver.timestamp)}`
  }
  
  if (approver.notes) {
    tooltip += `\nPesan: ${approver.notes}`
  }
  
  return tooltip
}
</script>

<style scoped>
.approval-timeline {
  @apply relative;
}

.timeline-container {
  @apply relative space-y-8;
  /* Ensure tooltips are not clipped */
  overflow: visible;
}

.timeline-item {
  @apply relative flex items-start space-x-4;
}

.timeline-line {
  @apply absolute left-6 top-12 w-0.5 h-full -ml-px;
  background: theme('colors.gray.200');
}

.timeline-line .timeline-progress {
  @apply w-full h-full rounded-full transition-all duration-500;
}

.timeline-node {
  @apply relative z-10 flex-shrink-0;
}

.timeline-node-base {
  @apply w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110;
}

.timeline-content {
  @apply flex-1 min-w-0;
}

.timeline-header {
  @apply flex items-center justify-between mb-3;
}

.timeline-title {
  @apply text-lg font-bold;
  color: theme('colors.gray.800');
}

.timeline-badge {
  @apply px-3 py-1 rounded-full text-xs font-semibold border transition-all duration-200;
}

.timeline-approvers {
  @apply space-y-3 mb-4;
}

.approver-item {
  @apply flex items-center space-x-3 p-3 rounded-lg border transition-all duration-200 hover:shadow-md;
  position: relative;
  /* Ensure tooltip container has proper stacking context */
  z-index: 1;
}

.approver-item:hover {
  z-index: 10;
}

.approver-avatar {
  @apply relative flex-shrink-0;
}

.avatar-circle {
  @apply w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200;
}

.approver-status-indicator {
  @apply absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow-sm;
}

.approver-info {
  @apply flex-1 min-w-0;
}

.approver-name {
  @apply font-medium text-sm;
  color: theme('colors.gray.800');
}

.approver-status {
  @apply text-xs font-medium;
}

.approver-timestamp {
  @apply text-xs font-medium opacity-60;
  color: theme('colors.gray.500');
}

.approver-notes-indicator {
  @apply flex items-center;
  color: theme('colors.gray.400');
}

.approver-tooltip {
  @apply absolute left-0 top-full mt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none;
  min-width: 250px;
  max-width: 300px;
  z-index: 9999;
  /* Ensure tooltip doesn't go off-screen */
  transform: translateX(-50%);
  left: 50%;
}

.tooltip-content {
  @apply bg-gray-900 text-white p-3 rounded-lg text-sm;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.tooltip-header {
  @apply flex items-center justify-between mb-2 pb-2 border-b border-gray-700;
}

.tooltip-message {
  @apply text-gray-200 leading-relaxed;
}

.timeline-timestamp {
  @apply flex items-center text-xs font-medium opacity-75;
  color: theme('colors.gray.500');
}

/* Dark mode styles */
:global(.dark) .timeline-title {
  color: theme('colors.gray.100');
}

:global(.dark) .approver-name {
  color: theme('colors.gray.200');
}

:global(.dark) .timeline-line {
  background: theme('colors.gray.700');
}

:global(.dark) .timeline-timestamp {
  color: theme('colors.gray.400');
}

:global(.dark) .approver-timestamp {
  color: theme('colors.gray.400');
}

:global(.dark) .approver-notes-indicator {
  color: theme('colors.gray.500');
}

:global(.dark) .tooltip-content {
  @apply bg-gray-800 border border-gray-700;
}

/* Animation for in-progress items */
.timeline-item-in-progress .timeline-node {
  animation: pulse-glow 2s ease-in-out infinite alternate;
}

@keyframes pulse-glow {
  from {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
  }
  to {
    box-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
  }
}

/* Glassmorphism effect */
.approver-item {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

:global(.dark) .approver-item {
  background: rgba(31, 41, 55, 0.3);
  border-color: rgba(75, 85, 99, 0.3);
}
</style>
