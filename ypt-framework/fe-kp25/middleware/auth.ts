export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // If user is not authenticated and trying to access protected routes
  if (!authStore.isAuthenticated) {
    // Allow access to public pages
    const publicPaths = ['/login', '/register', '/public', '/secure']
    const isPublicPath = publicPaths.some(path => to.path.startsWith(path))
    
    // Root path is also allowed (redirects based on auth state)
    if (to.path === '/' || isPublicPath) {
      return
    }
    
    // Redirect to login
    return navigateTo('/login')
  }
})
