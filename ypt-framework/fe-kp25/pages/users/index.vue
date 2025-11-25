<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-primary mb-1">Kelola Users</h2>
        <p class="text-muted mb-0">Administrasi user sistem</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">
        <svg class="me-2" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah User
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="users.length === 0" class="text-center py-5 text-muted">
          <p>Belum ada user</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0">Nama</th>
                <th class="border-0">Email</th>
                <th class="border-0">Role</th>
                <th class="border-0">Tanggal Dibuat</th>
                <th class="border-0">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="fw-medium">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="user.role === 'admin' ? 'bg-primary' : 'bg-secondary'">
                    {{ user.role }}
                  </span>
                </td>
                <td class="text-muted small">{{ formatDate(user.created_at) }}</td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" @click="openEditModal(user)">
                      Edit
                    </button>
                    <button
                      v-if="user.id !== authStore.user?.id"
                      class="btn btn-sm btn-outline-danger"
                      @click="confirmDelete(user)"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit User' : 'Tambah User' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveUser">
              <div class="mb-3">
                <label class="form-label">Nama *</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Email *</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control"
                  required
                />
              </div>
              <div v-if="!isEditing" class="mb-3">
                <label class="form-label">Password *</label>
                <input
                  v-model="form.password"
                  type="password"
                  class="form-control"
                  minlength="8"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Role *</label>
                <select v-model="form.role" class="form-select" required>
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button
              type="button"
              class="btn btn-primary"
              :disabled="saving"
              @click="saveUser"
            >
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              {{ isEditing ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { User } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const { getUsers, createUser, updateUser, deleteUser } = useUsers()
const authStore = useAuthStore()

const users = ref<User[]>([])
const isLoading = ref(true)

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user' as 'admin' | 'user',
})

const loadUsers = async () => {
  isLoading.value = true
  try {
    users.value = await getUsers()
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    isLoading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  editingId.value = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.role = 'user'
  showModal.value = true
}

const openEditModal = (user: User) => {
  isEditing.value = true
  editingId.value = user.id
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.role = user.role
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveUser = async () => {
  saving.value = true
  try {
    if (isEditing.value && editingId.value) {
      await updateUser(editingId.value, {
        name: form.name,
        email: form.email,
        role: form.role,
      })
    } else {
      await createUser({
        name: form.name,
        email: form.email,
        password: form.password,
        role: form.role,
      })
    }
    closeModal()
    loadUsers()
  } catch (error: any) {
    console.error('Failed to save user:', error)
    alert(error.response?.data?.message || 'Gagal menyimpan user')
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (user: User) => {
  if (confirm(`Apakah Anda yakin ingin menghapus user "${user.name}"?`)) {
    try {
      await deleteUser(user.id)
      loadUsers()
    } catch (error) {
      console.error('Failed to delete user:', error)
      alert('Gagal menghapus user')
    }
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  // Check if user is admin
  if (!authStore.isAdmin) {
    navigateTo('/dashboard')
    return
  }
  loadUsers()
})
</script>
