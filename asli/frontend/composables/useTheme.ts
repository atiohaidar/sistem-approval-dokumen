/**
 * Global Theme Management Composable
 * 
 * Provides centralized dark mode state management using Nuxt's useState
 * for reactive, SSR-safe theme switching across all components.
 * 
 * Usage:
 * ```typescript
 * const { isDark, toggleDarkMode, initTheme } = useTheme()
 * onMounted(() => initTheme())
 * ```
 */
export const useTheme = () => {
    // Global state shared across all components
    const isDark = useState<boolean>('isDark', () => false)

    /**
     * Initialize theme from localStorage
     * Should be called in layouts or root components onMounted
     */
    const initTheme = () => {
        if (import.meta.client) {
            const saved = localStorage.getItem('darkMode')
            isDark.value = saved === 'true'
            updateDocumentClass(isDark.value)
        }
    }

    /**
     * Toggle dark mode and persist to localStorage
     */
    const toggleDarkMode = () => {
        isDark.value = !isDark.value
        if (import.meta.client) {
            localStorage.setItem('darkMode', isDark.value.toString())
            updateDocumentClass(isDark.value)
        }
    }

    /**
     * Update document root class for global dark mode styles
     */
    const updateDocumentClass = (dark: boolean) => {
        if (import.meta.client) {
            if (dark) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    }

    return {
        isDark: readonly(isDark),
        toggleDarkMode,
        initTheme
    }
}
