<template>
  <div class="container-fluid py-4">
    <div class="mb-4">
      <NuxtLink to="/documents" class="text-primary text-decoration-none">
        ← Kembali ke Dokumen
      </NuxtLink>
      <h2 class="fw-bold text-primary mt-2">Upload Dokumen Baru</h2>
    </div>

    <form @submit.prevent="handleSubmit">
      <!-- Document Info -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
          <h5 class="mb-0 fw-semibold">Informasi Dokumen</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Judul Dokumen *</label>
            <input
              v-model="form.title"
              type="text"
              class="form-control"
              placeholder="Masukkan judul dokumen"
              required
            />
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Deskripsi</label>
            <textarea
              v-model="form.description"
              class="form-control"
              rows="3"
              placeholder="Deskripsi dokumen (opsional)"
            ></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">File Dokumen (PDF) *</label>
            <input
              type="file"
              accept=".pdf"
              class="form-control"
              @change="handleFileChange"
              required
            />
            <div class="form-text">Maksimal 10MB</div>
          </div>
        </div>
      </div>

      <!-- Approvers -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold">Konfigurasi Approval</h5>
          <button
            v-if="form.approvers.length < 10"
            type="button"
            class="btn btn-sm btn-outline-primary"
            @click="addLevel"
          >
            + Tambah Level
          </button>
        </div>
        <div class="card-body">
          <div v-for="(level, levelIndex) in form.approvers" :key="levelIndex" class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 fw-semibold">Level {{ levelIndex + 1 }}</h6>
              <button
                v-if="form.approvers.length > 1"
                type="button"
                class="btn btn-sm btn-outline-danger"
                @click="removeLevel(levelIndex)"
              >
                Hapus Level
              </button>
            </div>

            <div v-for="(approverId, approverIndex) in level" :key="approverIndex" class="mb-2">
              <div class="d-flex gap-2">
                <select
                  v-model="form.approvers[levelIndex]![approverIndex]"
                  class="form-select"
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
                  class="btn btn-outline-secondary"
                  @click="removeApprover(levelIndex, approverIndex)"
                >
                  Hapus
                </button>
              </div>
            </div>

            <button
              type="button"
              class="btn btn-sm btn-link text-primary p-0"
              @click="addApprover(levelIndex)"
            >
              + Tambah Approver di Level Ini
            </button>
          </div>
        </div>
      </div>

      <!-- QR Position -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
          <h5 class="mb-0 fw-semibold">Posisi QR Code</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-medium">Posisi X (0-1) *</label>
              <input
                v-model.number="form.qr_x"
                type="number"
                step="0.01"
                min="0"
                max="1"
                class="form-control"
                required
              />
              <div class="form-text">0 = kiri, 1 = kanan</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-medium">Posisi Y (0-1) *</label>
              <input
                v-model.number="form.qr_y"
                type="number"
                step="0.01"
                min="0"
                max="1"
                class="form-control"
                required
              />
              <div class="form-text">0 = atas, 1 = bawah</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-medium">Halaman *</label>
              <input
                v-model.number="form.qr_page"
                type="number"
                min="1"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-3">
              <label class="form-label fw-medium">Ukuran QR (0.05-0.5)</label>
              <input
                v-model.number="form.qr_size"
                type="number"
                step="0.01"
                min="0.05"
                max="0.5"
                class="form-control"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <!-- Submit -->
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Mengupload...' : 'Upload Dokumen' }}
        </button>
        <NuxtLink to="/documents" class="btn btn-secondary">Batal</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { User } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { createDocument } = useDocuments()
const { getUsers } = useUsers()
const router = useRouter()

const availableUsers = ref<User[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

interface FormState {
  title: string
  description: string
  file: File | null
  approvers: number[][]
  qr_x: number
  qr_y: number
  qr_page: number
  qr_size: number
}

const form = reactive<FormState>({
  title: '',
  description: '',
  file: null,
  approvers: [[0]],
  qr_x: 0.85,
  qr_y: 0.9,
  qr_page: 1,
  qr_size: 0.24,
})

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  form.file = file ?? null
}

const addLevel = () => {
  if (form.approvers.length < 10) {
    form.approvers.push([0])
  }
}

const removeLevel = (index: number) => {
  form.approvers.splice(index, 1)
}

const addApprover = (levelIndex: number) => {
  form.approvers[levelIndex]?.push(0)
}

const removeApprover = (levelIndex: number, approverIndex: number) => {
  form.approvers[levelIndex]?.splice(approverIndex, 1)
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    if (!form.file) {
      error.value = 'Pilih file PDF terlebih dahulu.'
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
    formData.append('qr_size', form.qr_size.toString())

    await createDocument(formData)
    router.push('/documents')
  } catch (err: any) {
    console.error('Error creating document:', err)
    error.value = err.response?.data?.message || 'Gagal mengupload dokumen.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    availableUsers.value = await getUsers()
  } catch (err) {
    console.error('Error loading users:', err)
  }
})
</script>
