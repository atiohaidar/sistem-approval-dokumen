<template>
  <div class="h-screen bg-telkom-white flex flex-col">
    <!-- Navigation -->
    <nav class="bg-telkom-white border-b border-telkom-grey shadow flex-shrink-0">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <!-- Hamburger Menu Button -->
            <button
              @click="toggleSidebar"
              class="mr-4 p-2 rounded-md text-telkom-grey-dark hover:bg-telkom-grey transition-colors duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
            </button>
            <h1 class="text-xl font-bold text-telkom-grey-dark">
              Sistem Approval Dokumen - Admin
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-telkom-grey-dark">
              {{ authStore.user?.name }}
            </span>
            <button
              @click="handleLogout"
              class="bg-telkom-red hover:bg-red-700 text-telkom-white px-4 py-2 rounded-md text-sm font-medium"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
      <AdminSidebar :is-open="isSidebarOpen" />

      <!-- Main Content -->
      <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-7xl mx-auto">
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-telkom-grey-dark">
              Manajemen User
            </h2>
            <button
              @click="showAddModal = true"
              class="bg-telkom-maroon hover:bg-telkom-red text-telkom-white px-4 py-2 rounded-md font-medium"
            >
              Tambah User
            </button>
          </div>

          <!-- Error Message -->
          <div v-if="userStore.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ userStore.error }}
          </div>

          <!-- Loading -->
          <div v-if="userStore.loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-telkom-maroon mx-auto"></div>
            <p class="mt-4 text-telkom-grey-dark">Memuat data...</p>
          </div>

          <!-- Users Table -->
          <div v-else class="bg-telkom-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-telkom-grey">
              <thead class="bg-telkom-grey">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                    Nama
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                    Role
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                    Dibuat
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-telkom-grey-dark uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-telkom-white divide-y divide-telkom-grey">
                <tr v-for="user in userStore.users" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-telkom-grey-dark">
                    {{ user.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-telkom-grey-dark">
                    {{ user.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="user.role === 'admin' ? 'bg-telkom-red text-telkom-white' : 'bg-telkom-grey text-telkom-grey-dark'"
                    >
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-telkom-grey-dark">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <button
                        @click="editUser(user)"
                        class="text-telkom-maroon hover:text-telkom-red"
                      >
                        Edit
                      </button>
                      <button
                        v-if="user.id !== authStore.user?.id"
                        @click="changeRole(user)"
                        class="text-telkom-grey-dark hover:text-telkom-maroon"
                      >
                        Role
                      </button>
                      <button
                        v-if="user.id !== authStore.user?.id"
                        @click="deleteUser(user)"
                        class="text-red-600 hover:text-red-900"
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
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-telkom-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-telkom-grey-dark mb-4">
            Tambah User Baru
          </h3>
          <form @submit.prevent="handleAddUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Nama</label>
              <input
                v-model="newUser.name"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Email</label>
              <input
                v-model="newUser.email"
                type="email"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Password</label>
              <input
                v-model="newUser.password"
                type="password"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Role</label>
              <select
                v-model="newUser.role"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="flex justify-end space-x-2">
              <button
                type="button"
                @click="showAddModal = false"
                class="px-4 py-2 text-sm font-medium text-telkom-grey-dark bg-telkom-grey rounded-md hover:bg-gray-300"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="userStore.loading"
                class="px-4 py-2 text-sm font-medium text-telkom-white bg-telkom-maroon rounded-md hover:bg-telkom-red disabled:opacity-50"
              >
                <span v-if="userStore.loading">Menyimpan...</span>
                <span v-else>Simpan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-telkom-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-telkom-grey-dark mb-4">
            Edit User
          </h3>
          <form @submit.prevent="handleEditUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Nama</label>
              <input
                v-model="editUserData.name"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Email</label>
              <input
                v-model="editUserData.email"
                type="email"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Role</label>
              <select
                v-model="editUserData.role"
                required
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="flex justify-end space-x-2">
              <button
                type="button"
                @click="showEditModal = false"
                class="px-4 py-2 text-sm font-medium text-telkom-grey-dark bg-telkom-grey rounded-md hover:bg-gray-300"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="userStore.loading"
                class="px-4 py-2 text-sm font-medium text-telkom-white bg-telkom-maroon rounded-md hover:bg-telkom-red disabled:opacity-50"
              >
                <span v-if="userStore.loading">Menyimpan...</span>
                <span v-else>Update</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-telkom-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-telkom-grey-dark mb-4">
            Konfirmasi Hapus
          </h3>
          <p class="text-sm text-telkom-grey-dark mb-4">
            Apakah Anda yakin ingin menghapus user "{{ userToDelete?.name }}"?
          </p>
          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showDeleteModal = false"
              class="px-4 py-2 text-sm font-medium text-telkom-grey-dark bg-telkom-grey rounded-md hover:bg-gray-300"
            >
              Batal
            </button>
            <button
              @click="confirmDelete"
              :disabled="userStore.loading"
              class="px-4 py-2 text-sm font-medium text-telkom-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              <span v-if="userStore.loading">Menghapus...</span>
              <span v-else>Hapus</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Role Modal -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-telkom-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-telkom-grey-dark mb-4">
            Ubah Role User
          </h3>
          <p class="text-sm text-telkom-grey-dark mb-4">
            Ubah role untuk user "{{ userToChangeRole?.name }}"
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-telkom-grey-dark">Role Baru</label>
              <select
                v-model="newRole"
                class="mt-1 block w-full px-3 py-2 border border-telkom-grey rounded-md shadow-sm focus:outline-none focus:ring-telkom-red focus:border-telkom-red"
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="flex justify-end space-x-2">
              <button
                type="button"
                @click="showRoleModal = false"
                class="px-4 py-2 text-sm font-medium text-telkom-grey-dark bg-telkom-grey rounded-md hover:bg-gray-300"
              >
                Batal
              </button>
              <button
                @click="confirmChangeRole"
                :disabled="userStore.loading"
                class="px-4 py-2 text-sm font-medium text-telkom-white bg-telkom-maroon rounded-md hover:bg-telkom-red disabled:opacity-50"
              >
                <span v-if="userStore.loading">Mengubah...</span>
                <span v-else>Ubah Role</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
import { useUserStore } from '~/stores/user'

definePageMeta({
  middleware: 'admin'
})

const authStore = useAuthStore()
const userStore = useUserStore()

// Sidebar state
const isSidebarOpen = ref(true)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

// Modal states
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showRoleModal = ref(false)

// Form data
const newUser = ref({
  name: '',
  email: '',
  password: '',
  role: 'user'
})

const editUserData = ref({
  id: null,
  name: '',
  email: '',
  role: 'user'
})

const userToDelete = ref(null)
const userToChangeRole = ref(null)
const newRole = ref('user')

// Fetch users on page load
onMounted(async () => {
  try {
    await userStore.fetchUsers()
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
})

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
}

const handleAddUser = async () => {
  try {
    await userStore.createUser(newUser.value)
    showAddModal.value = false
    newUser.value = { name: '', email: '', password: '', role: 'user' }
  } catch (error) {
    console.error('Failed to create user:', error)
  }
}

const editUser = (user) => {
  editUserData.value = { ...user }
  showEditModal.value = true
}

const handleEditUser = async () => {
  try {
    await userStore.updateUser(editUserData.value.id, editUserData.value)
    showEditModal.value = false
  } catch (error) {
    console.error('Failed to update user:', error)
  }
}

const deleteUser = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    await userStore.deleteUser(userToDelete.value.id)
    showDeleteModal.value = false
    userToDelete.value = null
  } catch (error) {
    console.error('Failed to delete user:', error)
  }
}

const changeRole = (user) => {
  userToChangeRole.value = user
  newRole.value = user.role
  showRoleModal.value = true
}

const confirmChangeRole = async () => {
  try {
    await userStore.changeUserRole(userToChangeRole.value.id, newRole.value)
    showRoleModal.value = false
    userToChangeRole.value = null
  } catch (error) {
    console.error('Failed to change role:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID')
}
</script>