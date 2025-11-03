import axios from 'axios'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const router = useRouter()

  const api = axios.create({
    baseURL: config.public.apiBase,
    withCredentials: true, // needed for Sanctum cookie-based auth
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  })

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      // We rely on httpOnly cookie 'access_token' set by server and server-side middleware
      // to authenticate requests. Do NOT attach tokens from JS-accessible storage.
      return config
    },
    (error) => {
      return Promise.reject(error)
    }
  )

  // Response interceptor
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        // Use clearAuth instead of logout to avoid calling useNuxtApp outside of proper context
        try {
          const auth = useAuthStore()
          auth.clearAuth()
        } catch (_) {
          // If even getting the store fails, redirect directly
          if (process.client) {
            window.location.href = '/login'
          }
        }
      }
      return Promise.reject(error)
    }
  )

  return {
    provide: {
      api,
    },
  }
})
