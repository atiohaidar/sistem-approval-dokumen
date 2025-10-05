export default defineNuxtRouteMiddleware((to, from) => {
    const authStore = useAuthStore()

    // Initialize auth state if not done
    if (!authStore.isAuthenticated) {
        authStore.initializeAuth()
    }

    // If not authenticated and trying to access protected route
    if (!authStore.isAuthenticated && to.path !== '/login' && to.path !== '/register') {
        return navigateTo('/login')
    }

    // If authenticated and trying to access auth pages, redirect to home
    if (authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
        return navigateTo('/')
    }
})