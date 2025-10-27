<template>
  <div>
    <div class="mb-8">
      <NuxtLink to="/documents" class="text-telkom-red hover:text-telkom-red-dark mb-4 inline-block">
        ← Kembali ke Dokumen
      </NuxtLink>
      <h1 class="text-3xl font-bold text-gray-800">Upload Dokumen Baru</h1>
      <p class="text-gray-600 mt-2">Isi form di bawah untuk upload dokumen baru</p>
    </div>

    <form @submit.prevent="handleSubmit" class="max-w-4xl">
      <!-- Basic Information -->
      <div class="card mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Dokumen</h2>
        
        <div class="space-y-4">
          <div>
            <label class="form-label">Judul Dokumen *</label>
            <input
              v-model="form.title"
              type="text"
              class="form-input"
              placeholder="Masukkan judul dokumen"
              required
            />
          </div>

          <div>
            <label class="form-label">Deskripsi</label>
            <textarea
              v-model="form.description"
              class="form-input"
              rows="3"
              placeholder="Masukkan deskripsi dokumen (opsional)"
            ></textarea>
          </div>

          <div>
            <label class="form-label">File Dokumen (PDF) *</label>
            <input
              type="file"
              accept=".pdf"
              @change="handleFileChange"
              class="form-input"
              required
            />
            <p class="text-sm text-gray-500 mt-1">Maksimal 10MB</p>
          </div>
        </div>
      </div>

      <!-- Approvers Configuration -->
      <div class="card mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Konfigurasi Approval</h2>
        
        <div v-for="(level, index) in form.approvers" :key="index" class="mb-6 p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-700">Level {{ index + 1 }}</h3>
            <button
              v-if="form.approvers.length > 1"
              type="button"
              @click="removeLevel(index)"
              class="text-red-600 hover:text-red-800 text-sm"
            >
              Hapus Level
            </button>
          </div>

          <div class="space-y-3">
            <div v-for="(approverId, approverIndex) in level" :key="approverIndex" class="flex gap-2">
              <select
                v-model="form.approvers[index]![approverIndex]"
                class="form-input flex-1"
                required
              >
                <option value="">Pilih Approver</option>
                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
              <button
                v-if="level.length > 1"
                type="button"
                @click="removeApprover(index, approverIndex)"
                class="btn btn-secondary text-sm"
              >
                Hapus
              </button>
            </div>

            <button
              type="button"
              @click="addApprover(index)"
              class="text-telkom-blue hover:text-telkom-blue-light text-sm font-medium"
            >
              + Tambah Approver di Level Ini
            </button>
          </div>
        </div>

        <button
          v-if="form.approvers.length < 10"
          type="button"
          @click="addLevel"
          class="btn btn-outline"
        >
          + Tambah Level Baru
        </button>
      </div>

      <!-- QR Code Position - Interactive -->
      <div class="card mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Posisi QR Code</h2>
        
        <!-- PDF Preview dengan Drag & Drop QR -->
        <div v-if="pdfPreviewUrl" class="mb-6">
          <div class="glass-strong rounded-xl p-6">
            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Preview Dokumen - Geser QR Code ke Posisi yang Diinginkan
            </h3>

            <!-- Page Selector (jika multi-page) -->
            <div v-if="pdfTotalPages > 1" class="mb-4 flex items-center gap-3 p-4 bg-blue-50 rounded-lg">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="text-sm font-medium text-blue-800">
                Dokumen memiliki <strong>{{ pdfTotalPages }} halaman</strong>
              </span>
              <div class="flex-1"></div>
              <label class="text-sm font-medium text-blue-800 mr-2">QR di Halaman:</label>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="changePage(-1)"
                  :disabled="form.qr_page <= 1"
                  class="px-3 py-1 bg-white border border-blue-300 rounded-lg text-blue-700 hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  ←
                </button>
                <span class="px-4 py-1 bg-white border-2 border-blue-500 rounded-lg font-bold text-blue-700 min-w-[3rem] text-center">
                  {{ form.qr_page }}
                </span>
                <button
                  type="button"
                  @click="changePage(1)"
                  :disabled="form.qr_page >= pdfTotalPages"
                  class="px-3 py-1 bg-white border border-blue-300 rounded-lg text-blue-700 hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  →
                </button>
              </div>
            </div>
            
            <!-- PDF Viewer dengan QR draggable -->
            <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="min-height: 600px;">
              <iframe
                ref="pdfViewer"
                :src="currentPageUrl"
                class="w-full h-full border-0"
                style="height: 600px;"
                @load="handlePdfLoad"
              ></iframe>
              
              <!-- QR Code Draggable Overlay -->
              <div
                ref="qrOverlay"
                class="absolute inset-0 pointer-events-none"
              >
                <div
                  class="absolute cursor-move pointer-events-auto"
                  :style="{
                    left: `${form.qr_x * 100}%`,
                    top: `${form.qr_y * 100}%`,
                    transform: 'translate(-50%, -50%)'
                  }"
                  @mousedown="startDragging"
                  @touchstart="startDragging"
                >
                  <!-- QR Code Visual -->
                  <div class="relative group">
                    <div class="w-20 h-20 bg-white border-4 border-blue-500 rounded-lg shadow-lg flex items-center justify-center animate-pulse-slow">
                      <svg class="w-12 h-12 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8zm-8-2h4V7H5v4zm10 0h4V7h-4v4zM5 19h4v-4H5v4zm10 0h4v-4h-4v4z"/>
                      </svg>
                    </div>
                    <!-- Tooltip dengan info halaman -->
                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                      Halaman {{ form.qr_page }} - Geser untuk posisi
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Coordinate Display dengan Page Info -->
            <div class="mt-4 flex items-center justify-between text-sm">
              <div class="flex gap-6">
                <span class="text-gray-600">
                  <strong>Halaman:</strong> {{ form.qr_page }} / {{ pdfTotalPages || '?' }}
                </span>
                <span class="text-gray-600">
                  <strong>X:</strong> {{ form.qr_x.toFixed(2) }}
                </span>
                <span class="text-gray-600">
                  <strong>Y:</strong> {{ form.qr_y.toFixed(2) }}
                </span>
              </div>
              <button
                type="button"
                @click="resetQRPosition"
                class="text-telkom-blue hover:text-telkom-blue-light font-medium"
              >
                Reset ke Posisi Default
              </button>
            </div>
          </div>
        </div>

        <!-- Manual Input (Fallback) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Posisi X (0.0 - 1.0) *</label>
            <input
              v-model.number="form.qr_x"
              type="number"
              step="0.01"
              min="0"
              max="1"
              class="form-input"
              required
            />
            <p class="text-sm text-gray-500 mt-1">0 = kiri, 1 = kanan</p>
          </div>

          <div>
            <label class="form-label">Posisi Y (0.0 - 1.0) *</label>
            <input
              v-model.number="form.qr_y"
              type="number"
              step="0.01"
              min="0"
              max="1"
              class="form-input"
              required
            />
            <p class="text-sm text-gray-500 mt-1">0 = atas, 1 = bawah</p>
          </div>

          <div>
            <label class="form-label">Halaman *</label>
            <input
              v-model.number="form.qr_page"
              type="number"
              min="1"
              :max="pdfTotalPages || 999"
              class="form-input"
              required
            />
            <p class="text-sm text-gray-500 mt-1">
              {{ pdfTotalPages ? `Total: ${pdfTotalPages} halaman` : 'Nomor halaman' }}
            </p>
          </div>
        </div>

        <div v-if="!pdfPreviewUrl" class="mt-4 p-4 bg-amber-50 rounded-lg">
          <p class="text-sm text-amber-800">
            <strong>💡 Tips:</strong> Upload file PDF terlebih dahulu untuk melihat preview interaktif, memilih halaman, dan mengatur posisi QR Code dengan drag & drop.
          </p>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="p-4 bg-red-100 text-red-700 rounded-lg mb-6">
        {{ error }}
      </div>

      <!-- Actions -->
      <div class="flex gap-4">
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="loading"
        >
          {{ loading ? 'Uploading...' : 'Upload Dokumen' }}
        </button>
        <NuxtLink to="/documents" class="btn btn-secondary">
          Batal
        </NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useDocuments } from '~/composables/useDocuments'
import { useUsers } from '~/composables/useUsers'
import type { User } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { createDocument } = useDocuments()
const { getUsers } = useUsers()
const router = useRouter()

const form = reactive({
  title: '',
  description: '',
  file: null as File | null,
  approvers: [[]] as number[][],
  qr_x: 0.85,
  qr_y: 0.9,
  qr_page: 1,
})

const availableUsers = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const pdfPreviewUrl = ref<string | null>(null)
const pdfTotalPages = ref<number>(0)
const pdfViewer = ref<HTMLIFrameElement | null>(null)
const qrOverlay = ref<HTMLElement | null>(null)
const isDragging = ref(false)

// Computed property untuk URL dengan page parameter
const currentPageUrl = computed(() => {
  if (!pdfPreviewUrl.value) return ''
  return `${pdfPreviewUrl.value}#page=${form.qr_page}`
})

const handleFileChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    form.file = file
    
    // Create preview URL for PDF
    if (file.type === 'application/pdf') {
      // Revoke old URL if exists
      if (pdfPreviewUrl.value) {
        URL.revokeObjectURL(pdfPreviewUrl.value)
      }
      pdfPreviewUrl.value = URL.createObjectURL(file)
      
      // Detect total pages using PDF.js (if available) or fallback
      try {
        await detectPdfPages(file)
      } catch (err) {
        console.warn('Could not detect PDF pages:', err)
        pdfTotalPages.value = 0
      }
    }
  }
}

// Detect PDF total pages
const detectPdfPages = async (file: File) => {
  try {
    // Use a simple approach: load PDF and check metadata
    // For production, consider using PDF.js library
    const arrayBuffer = await file.arrayBuffer()
    const text = new TextDecoder().decode(arrayBuffer)
    
    // Simple regex to find /Count in PDF structure (works for most PDFs)
    const match = text.match(/\/Count\s+(\d+)/)
    if (match && match[1]) {
      pdfTotalPages.value = parseInt(match[1], 10)
    } else {
      // Fallback: count /Page occurrences (less reliable)
      const pageMatches = text.match(/\/Type\s*\/Page[^s]/g)
      pdfTotalPages.value = pageMatches ? pageMatches.length : 1
    }
  } catch (err) {
    console.error('PDF page detection error:', err)
    pdfTotalPages.value = 1
  }
}

// Handle PDF iframe load
const handlePdfLoad = () => {
  // PDF loaded successfully
  console.log('PDF loaded on page:', form.qr_page)
}

// Change page navigation
const changePage = (delta: number) => {
  const newPage = form.qr_page + delta
  if (newPage >= 1 && newPage <= (pdfTotalPages.value || 999)) {
    form.qr_page = newPage
  }
}

const startDragging = (event: MouseEvent | TouchEvent) => {
  event.preventDefault()
  isDragging.value = true
  
  const moveHandler = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value || !qrOverlay.value) return
    
    const overlay = qrOverlay.value
    const rect = overlay.getBoundingClientRect()
    
    let clientX: number
    let clientY: number
    
    if (e instanceof MouseEvent) {
      clientX = e.clientX
      clientY = e.clientY
    } else {
      if (e.touches && e.touches[0]) {
        clientX = e.touches[0].clientX
        clientY = e.touches[0].clientY
      } else {
        return
      }
    }
    
    // Calculate relative position (0-1)
    const x = (clientX - rect.left) / rect.width
    const y = (clientY - rect.top) / rect.height
    
    // Clamp values between 0 and 1
    form.qr_x = Math.max(0, Math.min(1, x))
    form.qr_y = Math.max(0, Math.min(1, y))
  }
  
  const stopHandler = () => {
    isDragging.value = false
    document.removeEventListener('mousemove', moveHandler)
    document.removeEventListener('mouseup', stopHandler)
    document.removeEventListener('touchmove', moveHandler)
    document.removeEventListener('touchend', stopHandler)
  }
  
  document.addEventListener('mousemove', moveHandler)
  document.addEventListener('mouseup', stopHandler)
  document.addEventListener('touchmove', moveHandler)
  document.addEventListener('touchend', stopHandler)
}

const resetQRPosition = () => {
  form.qr_x = 0.85
  form.qr_y = 0.9
}

const addLevel = () => {
  if (form.approvers.length < 10) {
    form.approvers.push([])
  }
}

const removeLevel = (index: number) => {
  form.approvers.splice(index, 1)
}

const addApprover = (levelIndex: number) => {
  const level = form.approvers[levelIndex]
  if (level) {
    level.push(0)
  }
}

const removeApprover = (levelIndex: number, approverIndex: number) => {
  const level = form.approvers[levelIndex]
  if (level) {
    level.splice(approverIndex, 1)
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    // Validate approvers
    const validApprovers = form.approvers.filter(level => level.length > 0 && level.every(id => id > 0))
    
    if (validApprovers.length === 0) {
      error.value = 'Minimal harus ada 1 level approver'
      loading.value = false
      return
    }

    // Create FormData
    const formData = new FormData()
    formData.append('title', form.title)
    if (form.description) {
      formData.append('description', form.description)
    }
    if (form.file) {
      formData.append('file', form.file)
    }
    formData.append('approvers', JSON.stringify(validApprovers))
    formData.append('qr_x', form.qr_x.toString())
    formData.append('qr_y', form.qr_y.toString())
    formData.append('qr_page', form.qr_page.toString())

    await createDocument(formData)
    router.push('/documents')
  } catch (err: any) {
    console.error('Error creating document:', err)
    error.value = err.response?.data?.message || 'Gagal mengupload dokumen. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    availableUsers.value = await getUsers()
    // Initialize with one empty approver in first level
    const firstLevel = form.approvers[0]
    if (firstLevel && firstLevel.length === 0) {
      firstLevel.push(0)
    }
  } catch (err) {
    console.error('Error loading users:', err)
  }
})
</script>
