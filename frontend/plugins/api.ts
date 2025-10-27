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
        // Avoid infinite loop: if the failing request was the logout endpoint,
        // don't call auth.logout() which itself calls the logout endpoint.
        const reqUrl = error.config?.url || ''
        if (typeof reqUrl === 'string' && reqUrl.includes('/auth/logout')) {
          try {
            const auth = useAuthStore()
            auth.user = null
            // redirect to login
            router.push('/login')
          } catch (_) {
            router.push('/login')
          }
        } else {
          try {
            // Call auth store logout to ensure server-side cookie is cleared
            const auth = useAuthStore()
            auth.logout()
          } catch (_) {
            router.push('/login')
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
