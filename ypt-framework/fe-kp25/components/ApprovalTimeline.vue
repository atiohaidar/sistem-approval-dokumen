<template>
  <div class="approval-timeline">
    <div v-for="(level, index) in approvalLevels" :key="index" class="timeline-item mb-4">
      <div class="d-flex align-items-start">
        <!-- Timeline Node -->
        <div class="timeline-node me-3">
          <div 
            class="node-circle d-flex align-items-center justify-content-center rounded-circle"
            :class="getNodeClass(level.status)"
          >
            <template v-if="level.status === 'completed'">
              <svg class="bi" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
              </svg>
            </template>
            <template v-else-if="level.status === 'rejected'">
              <svg class="bi" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
              </svg>
            </template>
            <template v-else-if="level.status === 'in_progress'">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </template>
            <template v-else>
              <span class="text-muted">{{ index + 1 }}</span>
            </template>
          </div>
          <div v-if="index < approvalLevels.length - 1" class="timeline-line"></div>
        </div>

        <!-- Timeline Content -->
        <div class="timeline-content flex-grow-1">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-semibold">Level {{ index + 1 }}</h6>
            <span class="badge" :class="getBadgeClass(level.status)">
              {{ getStatusText(level.status) }}
            </span>
          </div>

          <!-- Approvers List -->
          <div class="approvers-list">
            <div 
              v-for="approver in level.approvers" 
              :key="approver.id"
              class="approver-item p-2 rounded mb-2"
              :class="getApproverClass(approver.status)"
            >
              <div class="d-flex align-items-center">
                <div 
                  class="avatar-circle me-2 d-flex align-items-center justify-content-center rounded-circle"
                  :class="getAvatarClass(approver.status)"
                >
                  {{ getInitials(approver.name) }}
                </div>
                <div class="flex-grow-1">
                  <div class="fw-medium small">{{ approver.name }}</div>
                  <div class="text-muted small">
                    {{ getApproverStatusText(approver.status) }}
                    <span v-if="approver.timestamp" class="ms-2">
                      · {{ formatTimestamp(approver.timestamp) }}
                    </span>
                  </div>
                </div>
                <span v-if="approver.notes" class="text-muted" :title="approver.notes">
                  <svg class="bi" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                  </svg>
                </span>
              </div>
            </div>
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
}

const props = defineProps<Props>()

const getNodeClass = (status: string) => {
  switch (status) {
    case 'completed': return 'bg-success text-white'
    case 'in_progress': return 'bg-warning text-dark'
    case 'rejected': return 'bg-danger text-white'
    default: return 'bg-secondary text-white'
  }
}

const getBadgeClass = (status: string) => {
  switch (status) {
    case 'completed': return 'bg-success'
    case 'in_progress': return 'bg-warning text-dark'
    case 'rejected': return 'bg-danger'
    default: return 'bg-secondary'
  }
}

const getApproverClass = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-success bg-opacity-10 border border-success'
    case 'rejected': return 'bg-danger bg-opacity-10 border border-danger'
    default: return 'bg-light border'
  }
}

const getAvatarClass = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-success text-white'
    case 'rejected': return 'bg-danger text-white'
    default: return 'bg-secondary text-white'
  }
}

const getStatusText = (status: string) => {
  const map: Record<string, string> = {
    completed: 'Selesai',
    in_progress: 'Berlangsung',
    rejected: 'Ditolak',
    pending: 'Menunggu'
  }
  return map[status] || status
}

const getApproverStatusText = (status: string) => {
  const map: Record<string, string> = {
    approved: 'Disetujui',
    rejected: 'Ditolak',
    pending: 'Menunggu'
  }
  return map[status] || status
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatTimestamp = (timestamp?: string) => {
  if (!timestamp) return ''
  return new Date(timestamp).toLocaleDateString('id-ID', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.node-circle {
  width: 40px;
  height: 40px;
  font-size: 0.875rem;
  font-weight: 600;
}

.avatar-circle {
  width: 32px;
  height: 32px;
  font-size: 0.75rem;
  font-weight: 600;
}

.timeline-line {
  width: 2px;
  height: 100%;
  min-height: 20px;
  background-color: #dee2e6;
  margin: 8px auto;
}

.timeline-node {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.approver-item {
  transition: all 0.2s ease;
}

.approver-item:hover {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
