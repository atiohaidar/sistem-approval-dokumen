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
      const token = useCookie('auth_token').value
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
      // Ensure CSRF header is set when cookie is present (Sanctum SPA)
      try {
        const xsrf = useCookie('XSRF-TOKEN').value
        if (xsrf) {
          config.headers['X-XSRF-TOKEN'] = xsrf
        }
      } catch (_) {
        // ignore if cookie not accessible
      }
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
        // Clear auth data
        useCookie('auth_token').value = null
        useCookie('user').value = null
        // Redirect to login
        router.push('/login')
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
