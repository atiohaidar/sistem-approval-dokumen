import { defineStore } from 'pinia'
import type { User, AuthResponse, LoginRequest, RegisterRequest } from '~/types/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    // token is not stored in JS; access token is stored httpOnly cookie by backend
    token: null as string | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
  isAuthenticated: (state) => !!state.user,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async login(credentials: LoginRequest) {
      this.loading = true
      this.error = null
      const { $api } = useNuxtApp()
      const config = useRuntimeConfig()

      try {
        // Directly call login endpoint. We use httpOnly access_token cookie set by backend
        // and axios is configured with withCredentials: true so cookies are handled automatically.
        const response = await $api.post<AuthResponse>('/auth/login', credentials)
        const { user } = response.data

        // Server sets httpOnly cookie 'access_token'. Keep only user in store.
        this.user = user

        return { success: true }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Login failed'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async register(data: RegisterRequest) {
      this.loading = true
      this.error = null
      const { $api } = useNuxtApp()
      const config = useRuntimeConfig()

      try {
        // Directly call register endpoint. Server will set httpOnly access_token cookie.
        const response = await $api.post<AuthResponse>('/auth/register', data)
        const { user } = response.data

        // Server sets httpOnly cookie 'access_token'. Keep only user in store.
        this.user = user

        return { success: true }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Registration failed'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        const { $api } = useNuxtApp()
        await $api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear state
        this.user = null
        this.token = null
        
        // Clear cookie if we're in a Nuxt context
        if (process.client) {
          try { 
            const cookie = useCookie('user')
            cookie.value = null 
          } catch (_) { }
        }

        // Redirect to login
        try {
          navigateTo('/login')
        } catch (_) {
          // If navigateTo fails (outside Nuxt context), use window.location
          if (process.client) {
            window.location.href = '/login'
          }
        }
      }
    },

    // Method to clear auth without making API call (for use in interceptors)
    clearAuth() {
      this.user = null
      this.token = null
      
      // Redirect to login using window.location (safe to call from anywhere)
      if (process.client) {
        window.location.href = '/login'
      }
    },

    async fetchUser() {
      try {
        const { $api } = useNuxtApp()
        const response = await $api.get<User>('/auth/user')
        this.user = response.data
      } catch (error) {
        console.error('Fetch user error:', error)
        // Don't call logout here as it may be called outside proper context
        // Just clear the user state
        this.user = null
        this.token = null
      }
    },

    async initializeFromCookie() {
      // Hydrate user state using server-side session (httpOnly cookie)
      // Safe to ignore errors: fetchUser already handles invalid sessions
      try {
        await this.fetchUser()
      } catch (error) {
        console.error('Failed to initialize auth from cookie:', error)
      }
    },
  },
})
