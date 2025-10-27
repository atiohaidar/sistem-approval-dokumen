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
                v-model="form.approvers[index][approverIndex]"
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

      <!-- QR Code Position -->
      <div class="card mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Posisi QR Code</h2>
        
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
              class="form-input"
              required
            />
            <p class="text-sm text-gray-500 mt-1">Nomor halaman</p>
          </div>
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-blue-800">
            <strong>Tip:</strong> QR Code akan ditempatkan pada dokumen Anda. Posisi 0.5, 0.5 adalah tengah halaman.
            Untuk sudut kanan bawah, gunakan X=0.9 dan Y=0.9.
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

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.file = target.files[0]
  }
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
  form.approvers[levelIndex].push(0)
}

const removeApprover = (levelIndex: number, approverIndex: number) => {
  form.approvers[levelIndex].splice(approverIndex, 1)
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
    if (form.approvers[0].length === 0) {
      form.approvers[0].push(0)
    }
  } catch (err) {
    console.error('Error loading users:', err)
  }
})
</script>
