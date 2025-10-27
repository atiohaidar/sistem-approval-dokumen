import { defineStore } from 'pinia'
import type { User, AuthResponse, LoginRequest, RegisterRequest } from '~/types/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },

  actions: {
    async login(credentials: LoginRequest) {
      this.loading = true
      this.error = null
      const { $api } = useNuxtApp()
      const config = useRuntimeConfig()

      try {
        // Optional: attempt CSRF cookie for setups using Sanctum SPA. Ignore if not configured.
        try {
          const apiOrigin = new URL(config.public.apiBase).origin
          await $api.get(`${apiOrigin}/sanctum/csrf-cookie`)
        } catch (_) { /* ignore */ }

        const response = await $api.post<AuthResponse>('/auth/login', credentials)
        const { user, token } = response.data

        this.user = user
        this.token = token

        // Save to cookies
        const authToken = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7 }) // 7 days
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })
        authToken.value = token
        userCookie.value = JSON.stringify(user)

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
        // Optional: attempt CSRF cookie for setups using Sanctum SPA. Ignore if not configured.
        try {
          const apiOrigin = new URL(config.public.apiBase).origin
          await $api.get(`${apiOrigin}/sanctum/csrf-cookie`)
        } catch (_) { /* ignore */ }

        const response = await $api.post<AuthResponse>('/auth/register', data)
        const { user, token } = response.data

        this.user = user
        this.token = token

        // Save to cookies
        const authToken = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7 })
        const userCookie = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })
        authToken.value = token
        userCookie.value = JSON.stringify(user)

        return { success: true }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Registration failed'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      const { $api } = useNuxtApp()

      try {
        await $api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear state and cookies
        this.user = null
        this.token = null
        useCookie('auth_token').value = null
        useCookie('user').value = null

        // Redirect to login
        navigateTo('/login')
      }
    },

    async fetchUser() {
      const { $api } = useNuxtApp()

      try {
        const response = await $api.get<User>('/auth/user')
        this.user = response.data
      } catch (error) {
        console.error('Fetch user error:', error)
        this.logout()
      }
    },

    initializeFromCookie() {
      const authToken = useCookie('auth_token')
      const userCookie = useCookie('user')

      // token may exist without user (e.g., after refresh); handle separately
      if (authToken.value) {
        this.token = authToken.value as unknown as string
      }

      if (userCookie.value) {
        try {
          const v: any = userCookie.value as any
          this.user = typeof v === 'string' ? JSON.parse(v) : v
        } catch (error) {
          console.error('Parse user cookie error:', error)
          // Fallback: clear invalid user cookie but keep token if present
          useCookie('user').value = null
          this.user = null
        }
      }
    },
  },
})
