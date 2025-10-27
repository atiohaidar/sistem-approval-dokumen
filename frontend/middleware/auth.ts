export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuthStore()

  // Ensure user state is hydrated (will use httpOnly cookie)
  if (!auth.user) {
    try {
      await auth.fetchUser()
    } catch (_) {
      // ignore
    }
  }

  const isAuth = !!auth.user

  // If not authenticated and trying to access protected route
  if (!isAuth && to.path !== '/login' && to.path !== '/register') {
    return navigateTo('/login')
  }

  // If authenticated and trying to access login/register
  if (isAuth && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/dashboard')
  }
})
