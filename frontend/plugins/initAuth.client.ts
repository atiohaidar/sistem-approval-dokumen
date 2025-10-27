export default defineNuxtPlugin(async () => {
    try {
        const auth = useAuthStore()
        // Attempt to hydrate user from backend using httpOnly cookie
        await auth.fetchUser()
    } catch (e) {
        // ignore — user likely not authenticated
    }
})
