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
        // Handle unauthorized safely without calling composables that require Nuxt app context
        const reqUrl = error.config?.url || ''
        try {
          const auth = useAuthStore()

          // If a logout flow is already in progress, just replace route to login
          if (auth.isLoggingOut) {
            router.replace('/login')
            return Promise.reject(error)
          }

          // If the failing request was the logout endpoint, just clear local state and redirect
          if (typeof reqUrl === 'string' && reqUrl.includes('/auth/logout')) {
            auth.$patch({ user: null, token: null })
            router.replace('/login')
            return Promise.reject(error)
          }

          // If the failing request was the session check (/auth/user), do NOT redirect to login.
          // This request is used to probe whether a visitor has a session; a 401 here should
          // not force a navigation away from public pages — just clear local user state.
          if (typeof reqUrl === 'string' && reqUrl.includes('/auth/user')) {
            auth.$patch({ user: null, token: null })
            return Promise.reject(error)
          }

          // Default: clear auth state and redirect to login. Avoid calling store.logout() here
          // because it may call useNuxtApp() when invoked outside plugin/setup and cause errors.
          auth.$patch({ user: null, token: null, isLoggingOut: true })
          router.replace('/login')
          // Reset flag after short delay to allow other flows to settle
          setTimeout(() => { try { auth.isLoggingOut = false } catch (_) {} }, 3000)
        } catch (e) {
          // If anything goes wrong, fallback to a simple redirect
          try { router.replace('/login') } catch (_) {}
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
