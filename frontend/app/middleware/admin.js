export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // Initialize auth state if not done
  if (!authStore.isAuthenticated) {
    authStore.initializeAuth()
  }

  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  // Check if user is admin
  if (authStore.userRole !== 'admin') {
    return navigateTo('/')
  }
})