<template>
  <div>
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
        <p class="text-gray-600 mt-2">Kelola users dan roles</p>
      </div>
      <button @click="showCreateModal = true" class="btn btn-primary">
        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah User
      </button>
    </div>

    <div class="card">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-telkom-red border-t-transparent"></div>
        <p class="text-gray-600 mt-2">Loading...</p>
      </div>

      <div v-else-if="users.length === 0" class="text-center py-8 text-gray-500">
        Tidak ada users
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">{{ user.name }}</td>
              <td class="px-4 py-3 text-gray-700">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span :class="user.role === 'admin' ? 'badge bg-telkom-blue text-white' : 'badge badge-pending'">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button @click="editUser(user)" class="text-telkom-blue hover:text-telkom-blue-light font-medium">
                    Edit
                  </button>
                  <button
                    v-if="user.id !== authStore.user?.id"
                    @click="handleDelete(user.id)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
          {{ showEditModal ? 'Edit User' : 'Tambah User Baru' }}
        </h3>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="form-label">Nama *</label>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              required
            />
          </div>

          <div>
            <label class="form-label">Email *</label>
            <input
              v-model="form.email"
              type="email"
              class="form-input"
              required
            />
          </div>

          <div v-if="showCreateModal">
            <label class="form-label">Password *</label>
            <input
              v-model="form.password"
              type="password"
              class="form-input"
              minlength="8"
              required
            />
          </div>

          <div>
            <label class="form-label">Role *</label>
            <select v-model="form.role" class="form-input" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div v-if="error" class="p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ error }}
          </div>

          <div class="flex gap-4">
            <button type="submit" class="btn btn-primary flex-1" :disabled="processing">
              {{ processing ? 'Processing...' : (showEditModal ? 'Update' : 'Create') }}
            </button>
            <button type="button" @click="closeModal" class="btn btn-secondary flex-1">
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useUsers } from '~/composables/useUsers'
import type { User } from '~/types/api'

definePageMeta({
  middleware: 'auth',
})

const authStore = useAuthStore()
const { 
  useUsersQuery,
  useCreateUserMutation,
  useUpdateUserMutation,
  useDeleteUserMutation,
} = useUsers()

// Redirect non-admin
if (!authStore.isAdmin) {
  navigateTo('/dashboard')
}

// Query for users
const { data: users, isLoading: loading } = useUsersQuery()

// Mutations
const createMutation = useCreateUserMutation()
const updateMutation = useUpdateUserMutation()
const deleteMutation = useDeleteUserMutation()

const processing = computed(() => 
  createMutation.isPending.value || updateMutation.isPending.value
)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const error = ref<string | null>(null)
const editingUserId = ref<number | null>(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user' as 'user' | 'admin',
})

const editUser = (user: User) => {
  editingUserId.value = user.id
  form.name = user.name
  form.email = user.email
  form.role = user.role
  showEditModal.value = true
}

const handleSubmit = async () => {
  error.value = null

  try {
    if (showEditModal.value && editingUserId.value) {
      await updateMutation.mutateAsync({
        id: editingUserId.value,
        data: {
          name: form.name,
          email: form.email,
          role: form.role,
        },
      })
    } else {
      await createMutation.mutateAsync({
        name: form.name,
        email: form.email,
        password: form.password,
        role: form.role,
      })
    }
    closeModal()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal menyimpan user'
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return

  try {
    await deleteMutation.mutateAsync(id)
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus user')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingUserId.value = null
  error.value = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.role = 'user'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
