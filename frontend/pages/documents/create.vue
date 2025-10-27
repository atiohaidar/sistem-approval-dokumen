<template>
  <div
    class="min-h-screen p-6 transition-colors duration-300"
    :class="isDark ? 'bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950 text-gray-100' : 'bg-gradient-to-br from-blue-50 via-white to-orange-50 text-gray-900'"
  >
    <div class="max-w-5xl mx-auto space-y-6">
      <div class="flex items-start justify-between gap-4 animate-fade-in-up">
        <div>
          <NuxtLink
            to="/documents"
            class="inline-flex items-center gap-2 font-semibold transition-colors mb-3"
            :class="isDark ? 'text-red-300 hover:text-red-200' : 'text-telkom-red hover:text-telkom-red-dark'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dokumen
          </NuxtLink>
          <h1 class="text-4xl md:text-5xl font-black mb-2">Upload Dokumen Baru</h1>
          <p class="text-lg" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
            Isi form di bawah untuk upload dokumen baru
          </p>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Information -->
        <GlassCard
          class="animate-fade-in-up transition-colors"
          :class="isDark ? 'bg-gray-900/70 border border-gray-700 text-gray-100' : 'bg-white/85'"
        >
          <h2 class="text-2xl font-bold mb-6">Informasi Dokumen</h2>

          <div class="grid gap-5">
            <div>
              <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                Judul Dokumen *
              </label>
              <input
                v-model="form.title"
                type="text"
                class="glass-input w-full"
                :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100 placeholder-gray-500' : ''"
                placeholder="Masukkan judul dokumen"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                Deskripsi
              </label>
              <textarea
                v-model="form.description"
                class="glass-input w-full min-h-[140px]"
                :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100 placeholder-gray-500' : ''"
                rows="4"
                placeholder="Masukkan deskripsi dokumen (opsional)"
              ></textarea>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-semibold" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                File Dokumen (PDF) *
              </label>
              <input
                type="file"
                accept=".pdf"
                @change="handleFileChange"
                class="glass-input w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-telkom-red file:text-white file:font-semibold"
                :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100' : ''"
                required
              />
              <p class="text-sm" :class="isDark ? 'text-gray-500' : 'text-gray-500'">Maksimal 10MB</p>
            </div>
          </div>
        </GlassCard>

        <!-- Approvers Configuration -->
        <GlassCard
          class="animate-fade-in-up transition-colors"
          :class="isDark ? 'bg-gray-900/70 border border-gray-700 text-gray-100' : 'bg-white/85'"
        >
          <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div>
              <h2 class="text-2xl font-bold">Konfigurasi Approval</h2>
              <p class="text-sm mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                Tambahkan approver untuk setiap level persetujuan.
              </p>
            </div>
            <button
              v-if="form.approvers.length < 10"
              type="button"
              @click="addLevel"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition"
              :class="isDark ? 'bg-white/10 text-gray-100 hover:bg-white/15' : 'bg-gradient-to-r from-telkom-red to-orange-500 text-white hover:shadow-lg'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Tambah Level Baru
            </button>
          </div>

          <div class="space-y-6">
            <div
              v-for="(level, index) in form.approvers"
              :key="`level-${index}`"
              class="rounded-2xl border p-5 transition"
              :class="isDark ? 'bg-gray-950/40 border-gray-700' : 'bg-white/80 border-white/60 shadow-sm'"
            >
              <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-lg font-semibold" :class="isDark ? 'text-gray-100' : 'text-gray-700'">
                  Level {{ index + 1 }}
                </h3>
                <button
                  v-if="form.approvers.length > 1"
                  type="button"
                  @click="removeLevel(index)"
                  class="inline-flex items-center gap-1 text-sm font-medium transition"
                  :class="isDark ? 'text-red-300 hover:text-red-200' : 'text-red-600 hover:text-red-700'"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Hapus Level
                </button>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(approverId, approverIndex) in level"
                  :key="`level-${index}-approver-${approverIndex}`"
                  class="flex flex-col sm:flex-row gap-2 sm:items-center"
                >
                  <select
                    v-model="form.approvers[index]![approverIndex]"
                    class="glass-input flex-1"
                    :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100' : ''"
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
                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm font-semibold transition"
                    :class="isDark ? 'bg-white/10 text-gray-200 hover:bg-white/15' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                  >
                    Hapus
                  </button>
                </div>

                <button
                  type="button"
                  @click="addApprover(index)"
                  class="inline-flex items-center gap-2 text-sm font-semibold transition"
                  :class="isDark ? 'text-blue-300 hover:text-blue-200' : 'text-telkom-blue hover:text-telkom-blue-light'"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Approver di Level Ini
                </button>
              </div>
            </div>
          </div>
        </GlassCard>

        <!-- QR Code Position -->
        <GlassCard
          class="animate-fade-in-up transition-colors"
          :class="isDark ? 'bg-gray-900/70 border border-gray-700 text-gray-100' : 'bg-white/85'"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-2xl font-bold">Posisi QR Code</h2>
              <p class="text-sm mt-1" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                Gunakan preview PDF untuk mengatur posisi QR Code secara akurat.
              </p>
            </div>
            <div v-if="pdfReady && pdfTotalPages > 1" class="flex items-center gap-3">
              <button
                type="button"
                @click="changePage(-1)"
                :disabled="form.qr_page <= 1 || pdfLoading"
                class="px-3 py-2 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                :class="isDark ? 'bg-white/10 text-gray-100 hover:bg-white/15' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                ←
              </button>
              <span class="px-4 py-2 rounded-lg font-semibold"
                :class="isDark ? 'bg-white/10 text-gray-100 border border-white/15' : 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 border border-blue-200'"
              >
                Halaman {{ form.qr_page }} / {{ pdfTotalPages }}
              </span>
              <button
                type="button"
                @click="changePage(1)"
                :disabled="form.qr_page >= pdfTotalPages || pdfLoading"
                class="px-3 py-2 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                :class="isDark ? 'bg-white/10 text-gray-100 hover:bg-white/15' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                →
              </button>
            </div>
          </div>

          <div class="space-y-6">
            <div
              class="relative rounded-2xl border overflow-hidden transition"
              :class="isDark ? 'bg-gray-950/60 border-gray-700' : 'bg-white/80 border-white/60 shadow-sm'"
            >
              <div v-if="pdfLoading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/55 text-white z-20">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-t-transparent" :class="isDark ? 'border-red-400' : 'border-telkom-red'"></div>
                <span class="font-semibold">Memuat preview PDF...</span>
              </div>

              <div v-else-if="pdfRendering" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 text-white z-10">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-t-transparent" :class="isDark ? 'border-blue-400' : 'border-blue-500'"></div>
                <span class="text-sm font-semibold">Merender halaman...</span>
              </div>

              <div v-if="!pdfReady && !pdfLoading" class="p-10 text-center space-y-3">
                <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center"
                  :class="isDark ? 'bg-white/10 text-gray-300' : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-500'"
                >
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <p class="text-base font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                  Upload file PDF untuk menampilkan preview interaktif.
                </p>
              </div>

              <div v-if="pdfReady" class="relative">
                <div ref="pdfContainer" class="relative">
                  <canvas ref="pdfCanvas" class="w-full h-auto block"></canvas>
                  <div class="absolute inset-0 pointer-events-none">
                    <div
                      class="absolute cursor-move pointer-events-auto"
                      :style="qrOverlayStyle"
                      @mousedown.prevent="startDragging"
                      @touchstart.prevent="startDragging"
                    >
                      <div class="relative group">
                        <div class="w-20 h-20 bg-white border-4 border-blue-500 rounded-xl shadow-lg flex items-center justify-center"
                          :class="isDark ? 'bg-gray-900/90 border-blue-400 shadow-blue-900/40' : ''"
                        >
                          <svg class="w-12 h-12" :class="isDark ? 'text-blue-300' : 'text-blue-500'" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8zm-8-2h4V7H5v4zm10 0h4V7h-4v4zM5 19h4v-4H5v4zm10 0h4v-4h-4v4z" />
                          </svg>
                        </div>
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 rounded-lg px-3 py-1 text-xs font-semibold transition opacity-0 group-hover:opacity-100"
                          :class="isDark ? 'bg-gray-800 text-gray-100' : 'bg-gray-900 text-white'"
                        >
                          Halaman {{ form.qr_page }} · Geser untuk ubah posisi
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                  Posisi X (0.0 - 1.0) *
                </label>
                <input
                  v-model.number="form.qr_x"
                  type="number"
                  step="0.01"
                  min="0"
                  max="1"
                  class="glass-input w-full"
                  :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100' : ''"
                  required
                />
                <p class="text-sm mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-500'">
                  0 = kiri, 1 = kanan
                </p>
              </div>

              <div>
                <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                  Posisi Y (0.0 - 1.0) *
                </label>
                <input
                  v-model.number="form.qr_y"
                  type="number"
                  step="0.01"
                  min="0"
                  max="1"
                  class="glass-input w-full"
                  :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100' : ''"
                  required
                />
                <p class="text-sm mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-500'">
                  0 = atas, 1 = bawah
                </p>
              </div>

              <div>
                <label class="block text-sm font-semibold mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
                  Halaman *
                </label>
                <input
                  v-model.number="form.qr_page"
                  type="number"
                  min="1"
                  :max="pdfTotalPages || 1"
                  class="glass-input w-full"
                  :class="isDark ? 'bg-gray-950/60 border border-gray-700 text-gray-100' : ''"
                  required
                />
                <p class="text-sm mt-1" :class="isDark ? 'text-gray-500' : 'text-gray-500'">
                  {{ pdfTotalPages ? `Total: ${pdfTotalPages} halaman` : 'Nomor halaman' }}
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
              <div class="flex flex-wrap gap-4" :class="isDark ? 'text-gray-400' : 'text-gray-600'">
                <span><strong>X:</strong> {{ form.qr_x.toFixed(2) }}</span>
                <span><strong>Y:</strong> {{ form.qr_y.toFixed(2) }}</span>
                <span><strong>Halaman:</strong> {{ form.qr_page }} / {{ pdfTotalPages || '?' }}</span>
              </div>
              <button
                type="button"
                @click="resetQRPosition"
                class="inline-flex items-center gap-2 font-semibold transition"
                :class="isDark ? 'text-blue-300 hover:text-blue-200' : 'text-telkom-blue hover:text-telkom-blue-light'"
              >
                Reset ke Posisi Default
              </button>
            </div>

            <div v-if="pdfError" class="rounded-xl border px-4 py-3" :class="isDark ? 'border-red-500/50 bg-red-500/10 text-red-200' : 'border-red-200 bg-red-50 text-red-700'">
              {{ pdfError }}
            </div>
          </div>
        </GlassCard>

        <div v-if="error" class="glass-alert glass-alert-danger animate-fade-in-up">
          {{ error }}
        </div>

        <div class="flex flex-wrap gap-4 pt-2">
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="loading"
          >
            {{ loading ? 'Uploading...' : 'Upload Dokumen' }}
          </button>
          <NuxtLink
            to="/documents"
            class="btn btn-secondary"
          >
            Batal
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useDocuments } from '~/composables/useDocuments'
import { useUsers } from '~/composables/useUsers'
import type { User } from '~/types/api'
import type { PDFDocumentProxy, RenderTask } from 'pdfjs-dist/types/src/display/api'

definePageMeta({
  middleware: 'auth',
})

const { isDark } = useTheme()
const { createDocument } = useDocuments()
const { getUsers } = useUsers()
const router = useRouter()

interface FormState {
  title: string
  description: string
  file: File | null
  approvers: number[][]
  qr_x: number
  qr_y: number
  qr_page: number
}

const form = reactive<FormState>({
  title: '',
  description: '',
  file: null,
  approvers: [[]],
  qr_x: 0.85,
  qr_y: 0.9,
  qr_page: 1,
})

const availableUsers = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

const pdfTotalPages = ref(0)
const pdfDoc = shallowRef<PDFDocumentProxy | null>(null)
const pdfCanvas = ref<HTMLCanvasElement | null>(null)
const pdfContainer = ref<HTMLElement | null>(null)
const pdfLoading = ref(false)
const pdfRendering = ref(false)
const pdfError = ref<string | null>(null)
const isDragging = ref(false)
const activeRenderTask = shallowRef<RenderTask | null>(null)
const pdfReady = computed(() => !!pdfDoc.value)

type PdfJsModule = typeof import('pdfjs-dist/build/pdf')
let pdfJsModule: PdfJsModule | null = null

const ensurePdfJs = async (): Promise<PdfJsModule | null> => {
  if (!import.meta.client) return null
  if (pdfJsModule) return pdfJsModule

  const pdfLib = await import('pdfjs-dist/build/pdf')
  const worker = await import('pdfjs-dist/build/pdf.worker?url')
  pdfLib.GlobalWorkerOptions.workerSrc = worker.default
  pdfJsModule = pdfLib
  return pdfJsModule
}

const clamp = (value: number, min = 0, max = 1) => Math.min(Math.max(value, min), max)

const cleanupRender = () => {
  if (activeRenderTask.value) {
    try {
      activeRenderTask.value.cancel()
    } catch (err) {
      console.debug('Render cancel warning:', err)
    }
    activeRenderTask.value = null
  }
}

const cleanupDocument = async () => {
  cleanupRender()
  pdfRendering.value = false
  if (pdfDoc.value) {
    try {
      await pdfDoc.value.destroy()
    } catch (err) {
      console.debug('PDF destroy warning:', err)
    }
    pdfDoc.value = null
  }
  pdfTotalPages.value = 0
}

const renderPage = async (pageNumber: number) => {
  if (!pdfDoc.value || !pdfCanvas.value) return

  cleanupRender()
  pdfRendering.value = true

  try {
    const page = await pdfDoc.value.getPage(pageNumber)
    const viewport = page.getViewport({ scale: 1 })
    const containerWidth = pdfContainer.value?.clientWidth || viewport.width
    const scale = containerWidth / viewport.width
    const scaledViewport = page.getViewport({ scale })
    const canvas = pdfCanvas.value
    const context = canvas.getContext('2d')

    if (!context) {
      pdfRendering.value = false
      return
    }

    canvas.height = scaledViewport.height
    canvas.width = scaledViewport.width
    canvas.style.width = '100%'
    canvas.style.height = 'auto'

    const renderTask = page.render({ canvasContext: context, viewport: scaledViewport })
    activeRenderTask.value = renderTask
    await renderTask.promise
  } catch (err: any) {
    if (err?.name !== 'RenderingCancelledException') {
      console.error('PDF render error:', err)
      pdfError.value = 'Gagal menampilkan halaman PDF.'
    }
  } finally {
    pdfRendering.value = false
  }
}

const loadPdfDocument = async (file: File) => {
  const pdfLib = await ensurePdfJs()
  if (!pdfLib) return

  pdfLoading.value = true
  pdfError.value = null

  try {
    await cleanupDocument()
    const arrayBuffer = await file.arrayBuffer()
    const loadingTask = pdfLib.getDocument({ data: arrayBuffer })
    pdfDoc.value = await loadingTask.promise
    pdfTotalPages.value = pdfDoc.value.numPages || 1
    form.qr_page = 1
    await nextTick()
    await renderPage(form.qr_page)
  } catch (err: any) {
    console.error('PDF load error:', err)
    pdfError.value = 'Gagal memuat preview PDF. Pastikan file PDF tidak rusak.'
    await cleanupDocument()
  } finally {
    pdfLoading.value = false
  }
}

const handleFileChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  form.file = file ?? null
  pdfError.value = null

  if (!file) {
    await cleanupDocument()
    return
  }

  if (file.type !== 'application/pdf') {
    pdfError.value = 'File harus berformat PDF.'
    form.file = null
    target.value = ''
    await cleanupDocument()
    return
  }

  await loadPdfDocument(file)
}

const changePage = (delta: number) => {
  if (!pdfReady.value) return
  const maxPage = pdfTotalPages.value || 1
  const newPage = clamp(form.qr_page + delta, 1, maxPage)
  if (newPage !== form.qr_page) {
    form.qr_page = newPage
  }
}

const getClientPoint = (event: MouseEvent | TouchEvent) => {
  if (event instanceof MouseEvent) {
    return { x: event.clientX, y: event.clientY }
  }
  const touch = event.touches?.[0] || event.changedTouches?.[0]
  if (!touch) return null
  return { x: touch.clientX, y: touch.clientY }
}

const updatePositionFromEvent = (event: MouseEvent | TouchEvent) => {
  const point = getClientPoint(event)
  if (!point || !pdfCanvas.value) return
  const rect = pdfCanvas.value.getBoundingClientRect()
  if (!rect.width || !rect.height) return

  const relativeX = (point.x - rect.left) / rect.width
  const relativeY = (point.y - rect.top) / rect.height

  form.qr_x = clamp(relativeX)
  form.qr_y = clamp(relativeY)
}

const startDragging = (event: MouseEvent | TouchEvent) => {
  if (!pdfReady.value) return
  isDragging.value = true
  updatePositionFromEvent(event)

  const moveHandler = (moveEvent: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return
    updatePositionFromEvent(moveEvent)
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
  if (form.approvers.length >= 10) return
  form.approvers.push([0])
}

const removeLevel = (index: number) => {
  form.approvers.splice(index, 1)
}

const addApprover = (levelIndex: number) => {
  const level = form.approvers[levelIndex]
  if (!level) return
  level.push(0)
}

const removeApprover = (levelIndex: number, approverIndex: number) => {
  const level = form.approvers[levelIndex]
  if (!level) return
  level.splice(approverIndex, 1)
}

const qrOverlayStyle = computed(() => ({
  left: `${form.qr_x * 100}%`,
  top: `${form.qr_y * 100}%`,
  transform: 'translate(-50%, -50%)',
}))

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    if (!form.file) {
      error.value = 'Silakan pilih file PDF terlebih dahulu.'
      return
    }

    const validApprovers = form.approvers
      .map(level => level.filter(id => id > 0))
      .filter(level => level.length > 0)

    if (validApprovers.length === 0) {
      error.value = 'Minimal harus ada 1 level approver.'
      return
    }

    const formData = new FormData()
    formData.append('title', form.title)
    if (form.description) {
      formData.append('description', form.description)
    }
    formData.append('file', form.file)
    formData.append('approvers', JSON.stringify(validApprovers))
    formData.append('qr_x', form.qr_x.toString())
    formData.append('qr_y', form.qr_y.toString())
    formData.append('qr_page', form.qr_page.toString())

    await createDocument(formData)
    await router.push('/documents')
  } catch (err: any) {
    console.error('Error creating document:', err)
    error.value = err.response?.data?.message || 'Gagal mengupload dokumen. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

const enforceBounds = (value: number) => clamp(Number.isFinite(value) ? value : 0)

watch(() => form.qr_page, async (page) => {
  if (!pdfReady.value) return
  const maxPage = pdfTotalPages.value || 1
  const clampedPage = Math.min(Math.max(page, 1), maxPage)
  if (clampedPage !== page) {
    form.qr_page = clampedPage
    return
  }
  await renderPage(clampedPage)
})

watch(() => form.qr_x, (value) => {
  const normalized = enforceBounds(value)
  if (normalized !== value) {
    form.qr_x = normalized
  }
})

watch(() => form.qr_y, (value) => {
  const normalized = enforceBounds(value)
  if (normalized !== value) {
    form.qr_y = normalized
  }
})

let resizeTimeout: ReturnType<typeof setTimeout> | null = null
const handleResize = () => {
  if (!pdfReady.value) return
  if (resizeTimeout) {
    clearTimeout(resizeTimeout)
  }
  resizeTimeout = window.setTimeout(() => {
    renderPage(form.qr_page)
  }, 150)
}

onMounted(async () => {
  try {
    availableUsers.value = await getUsers()
    if (form.approvers[0]?.length === 0) {
      form.approvers[0].push(0)
    }
  } catch (err) {
    console.error('Error loading users:', err)
  }

  if (import.meta.client) {
    window.addEventListener('resize', handleResize)
  }
})

onBeforeUnmount(async () => {
  if (import.meta.client) {
    window.removeEventListener('resize', handleResize)
  }
  await cleanupDocument()
})
</script>
