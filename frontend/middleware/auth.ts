export default defineNuxtRouteMiddleware(async (to, from) => {
  // Only attempt to hydrate and redirect on the client — avoid SSR fetchUser which lacks browser cookies.
  const auth = useAuthStore()

  if (process.client) {
    // Ensure user state is hydrated (will use httpOnly cookie sent by browser)
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
  }
  // On server we do nothing (avoid redirects/server-side fetch)
})
