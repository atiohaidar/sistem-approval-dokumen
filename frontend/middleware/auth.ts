export default defineNuxtRouteMiddleware((to, from) => {
  const authToken = useCookie('auth_token')

  // If not authenticated and trying to access protected route
  if (!authToken.value && to.path !== '/login' && to.path !== '/register') {
    return navigateTo('/login')
  }

  // If authenticated and trying to access login/register
  if (authToken.value && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/dashboard')
  }
})
