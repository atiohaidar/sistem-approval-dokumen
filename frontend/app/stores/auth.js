import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated,
    userRole: (state) => state.user?.role || null
  },

  actions: {
    async login(credentials) {
      try {
        const config = useRuntimeConfig()
        const response = await $fetch(`${config.public.apiBase}/api/auth/login`, {
          method: 'POST',
          body: credentials
        })

        this.token = response.token
        this.user = response.user
        this.isAuthenticated = true

        // Store token and user data in localStorage for persistence
        if (process.client) {
          localStorage.setItem('auth_token', this.token)
          localStorage.setItem('auth_user', JSON.stringify(this.user))
        }

        return response
      } catch (error) {
        throw error
      }
    },

    async register(userData) {
      try {
        const config = useRuntimeConfig()
        const response = await $fetch(`${config.public.apiBase}/api/auth/register`, {
          method: 'POST',
          body: userData
        })

        this.token = response.token
        this.user = response.user
        this.isAuthenticated = true

        if (process.client) {
          localStorage.setItem('auth_token', this.token)
          localStorage.setItem('auth_user', JSON.stringify(this.user))
        }

        return response
      } catch (error) {
        throw error
      }
    },

    async logout() {
      try {
        const config = useRuntimeConfig()
        await $fetch(`${config.public.apiBase}/api/auth/logout`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        })
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const config = useRuntimeConfig()
        const user = await $fetch(`${config.public.apiBase}/api/auth/user`, {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        })

        this.user = user
        this.isAuthenticated = true

        // Update user data in localStorage
        if (process.client) {
          localStorage.setItem('auth_user', JSON.stringify(this.user))
        }
      } catch (error) {
        this.clearAuth()
        throw error
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false

      if (process.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    initializeAuth() {
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        const user = localStorage.getItem('auth_user')

        if (token && user) {
          this.token = token
          this.user = JSON.parse(user)
          this.isAuthenticated = true
        }
      }
    }
  }
})