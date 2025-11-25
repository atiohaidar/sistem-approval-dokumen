// Minimal global auth middleware.
// Original logic was commented out; provide a safe no-op middleware
// so Nuxt won't register an undefined middleware reference.
export default defineNuxtRouteMiddleware((to) => {
	// No-op: keep global auth placeholder active.
	// If you want to restore auth checks, uncomment and adapt the original logic.
});
