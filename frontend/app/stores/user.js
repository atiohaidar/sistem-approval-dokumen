import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    loading: false,
    error: null
  }),

  getters: {
    getUserById: (state) => (id) => {
      return state.users.find(user => user.id === id)
    },
    adminUsers: (state) => {
      return state.users.filter(user => user.role === 'admin')
    },
    regularUsers: (state) => {
      return state.users.filter(user => user.role === 'user')
    }
  },

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()

        const response = await $fetch(`${config.public.apiBase}/api/users`, {
          headers: {
            Authorization: `Bearer ${authStore.token}`
          }
        })

        this.users = response
      } catch (error) {
        this.error = error.data?.message || 'Failed to fetch users'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()

        const response = await $fetch(`${config.public.apiBase}/api/users`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`
          },
          body: userData
        })

        this.users.push(response)
        return response
      } catch (error) {
        this.error = error.data?.message || 'Failed to create user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateUser(userId, userData) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()

        const response = await $fetch(`${config.public.apiBase}/api/users/${userId}`, {
          method: 'PUT',
          headers: {
            Authorization: `Bearer ${authStore.token}`
          },
          body: userData
        })

        const index = this.users.findIndex(user => user.id === userId)
        if (index !== -1) {
          this.users[index] = response
        }

        return response
      } catch (error) {
        this.error = error.data?.message || 'Failed to update user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteUser(userId) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()

        await $fetch(`${config.public.apiBase}/api/users/${userId}`, {
          method: 'DELETE',
          headers: {
            Authorization: `Bearer ${authStore.token}`
          }
        })

        this.users = this.users.filter(user => user.id !== userId)
      } catch (error) {
        this.error = error.data?.message || 'Failed to delete user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async changeUserRole(userId, newRole) {
      this.loading = true
      this.error = null

      try {
        const config = useRuntimeConfig()
        const authStore = useAuthStore()

        const response = await $fetch(`${config.public.apiBase}/api/users/${userId}/change-role`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${authStore.token}`
          },
          body: { role: newRole }
        })

        const index = this.users.findIndex(user => user.id === userId)
        if (index !== -1) {
          this.users[index].role = newRole
        }

        return response
      } catch (error) {
        this.error = error.data?.message || 'Failed to change user role'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})