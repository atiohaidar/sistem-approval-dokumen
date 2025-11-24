<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Token Akses</h2>
        <p class="text-sm text-gray-600 mt-1">Kelola akses aman untuk dokumen ini</p>
      </div>
      <button 
        @click="showGenerateModal = true" 
        class="btn btn-primary"
        :disabled="loading"
      >
        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat Token Baru
      </button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-red-800">{{ error }}</p>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <p class="text-sm text-green-800">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && tokens.length === 0" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
      <p class="text-gray-600 mt-2">Memuat...</p>
    </div>

    <!-- Tokens List -->
    <div v-else-if="tokens.length > 0" class="space-y-4">
      <div 
        v-for="token in tokens" 
        :key="token.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-sm font-medium text-gray-900">
                {{ token.metadata?.purpose || 'Token Akses' }}
              </span>
              <span 
                class="text-xs px-2 py-1 rounded-full"
                :class="isExpired(token.expires_at) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
              >
                {{ isExpired(token.expires_at) ? 'Kedaluwarsa' : 'Aktif' }}
              </span>
            </div>
            
            <div class="space-y-1 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ token.generated_by.name }}</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kedaluwarsa: {{ formatExpiresIn(token.expires_at) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Diakses {{ token.access_count }} kali</span>
                <span v-if="token.last_accessed_at" class="text-gray-500">
                  (terakhir: {{ formatDate(token.last_accessed_at) }})
                </span>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <button 
              @click="handleCopyUrl(token.id)"
              class="btn-icon"
              title="Salin Link"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </button>
            <button 
              @click="handleRotate(token.id)"
              class="btn-icon"
              title="Rotasi Token"
              :disabled="loading"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
            <button 
              @click="handleRevoke(token.id)"
              class="btn-icon text-red-600 hover:bg-red-50"
              title="Cabut Token"
              :disabled="loading"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      <p class="text-gray-600 mb-4">Belum ada token akses</p>
      <button @click="showGenerateModal = true" class="btn btn-primary">
        Buat Token Pertama
      </button>
    </div>

    <!-- Generate Token Modal -->
    <div 
      v-if="showGenerateModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showGenerateModal = false"
    >
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Buat Token Akses Baru</h3>
        
        <form @submit.prevent="handleGenerate" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tujuan / Deskripsi
            </label>
            <input 
              v-model="generateForm.purpose"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-telkom-red"
              placeholder="Misal: Untuk reviewer eksternal"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Masa Berlaku (jam)
            </label>
            <select 
              v-model="generateForm.expires_in_hours"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-telkom-red"
            >
              <option :value="24">24 jam (1 hari)</option>
              <option :value="48">48 jam (2 hari)</option>
              <option :value="72">72 jam (3 hari)</option>
              <option :value="168">168 jam (1 minggu)</option>
              <option :value="720">720 jam (1 bulan)</option>
              <option :value="8760">8760 jam (1 tahun)</option>
            </select>
          </div>

          <div class="flex gap-3 pt-4">
            <button 
              type="button"
              @click="showGenerateModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              :disabled="loading"
            >
              Batal
            </button>
            <button 
              type="submit"
              class="flex-1 btn btn-primary"
              :disabled="loading"
            >
              {{ loading ? 'Membuat...' : 'Buat Token' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Generated Token Modal -->
    <div 
      v-if="generatedTokenUrl" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="generatedTokenUrl = null"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Token Berhasil Dibuat!</h3>
        </div>
        
        <p class="text-gray-600 mb-4">
          Salin link di bawah ini dan bagikan kepada orang yang berhak mengakses dokumen:
        </p>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
          <div class="flex items-center gap-2">
            <input 
              :value="generatedTokenUrl"
              readonly
              class="flex-1 bg-transparent text-sm text-gray-700 focus:outline-none"
            />
            <button 
              @click="copyGeneratedUrl"
              class="btn-icon"
              title="Salin"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </button>
          </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm text-yellow-800">
              <strong>Penting:</strong> Link ini memberikan akses ke dokumen. Bagikan hanya kepada orang yang berwenang.
            </p>
          </div>
        </div>

        <button 
          @click="generatedTokenUrl = null"
          class="w-full btn btn-primary"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useDocumentTokens } from '~/composables/useDocumentTokens'

const props = defineProps<{
  documentId: number
}>()

const {
  tokens,
  loading,
  error,
  generateToken,
  fetchTokens,
  revokeToken,
  rotateToken,
  copyTokenUrl,
  formatExpiresIn,
} = useDocumentTokens()

const showGenerateModal = ref(false)
const generatedTokenUrl = ref<string | null>(null)
const successMessage = ref<string | null>(null)

const generateForm = ref({
  purpose: '',
  expires_in_hours: 24,
})

onMounted(() => {
  fetchTokens(props.documentId)
})

const handleGenerate = async () => {
  const result = await generateToken(props.documentId, generateForm.value)
  
  if (result) {
    generatedTokenUrl.value = result.access_url
    showGenerateModal.value = false
    generateForm.value = { purpose: '', expires_in_hours: 24 }
    await fetchTokens(props.documentId)
  }
}

const handleCopyUrl = async (tokenId: number) => {
  // Note: For security, the actual token string is not exposed in the token list API
  // Users need to copy the URL when token is first generated
  // This provides information that the feature is not available for existing tokens
  successMessage.value = 'Token URL hanya dapat disalin saat pertama kali dibuat. Gunakan rotasi untuk mendapatkan URL baru.'
  setTimeout(() => { successMessage.value = null }, 5000)
}

const handleRotate = async (tokenId: number) => {
  if (!confirm('Rotasi token akan membuat token baru dan mencabut token lama. Lanjutkan?')) return

  const result = await rotateToken(props.documentId, tokenId)
  
  if (result) {
    generatedTokenUrl.value = result.access_url
    successMessage.value = 'Token berhasil dirotasi!'
    setTimeout(() => { successMessage.value = null }, 3000)
    await fetchTokens(props.documentId)
  }
}

const handleRevoke = async (tokenId: number) => {
  if (!confirm('Yakin ingin mencabut token ini? Akses akan langsung dinonaktifkan.')) return

  const success = await revokeToken(props.documentId, tokenId, 'Dicabut oleh pengguna')
  
  if (success) {
    successMessage.value = 'Token berhasil dicabut!'
    setTimeout(() => { successMessage.value = null }, 3000)
    await fetchTokens(props.documentId)
  }
}

const copyGeneratedUrl = async () => {
  if (generatedTokenUrl.value) {
    const success = await copyTokenUrl(generatedTokenUrl.value)
    if (success) {
      successMessage.value = 'Link berhasil disalin!'
      setTimeout(() => { successMessage.value = null }, 3000)
    }
  }
}

const isExpired = (expiresAt: string): boolean => {
  return new Date(expiresAt) < new Date()
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.btn {
  @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
}

.btn-primary {
  @apply bg-telkom-red text-white hover:bg-telkom-red-dark disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-icon {
  @apply p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed;
}
</style>
