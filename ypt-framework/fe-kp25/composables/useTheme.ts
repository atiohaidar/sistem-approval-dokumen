import { ref } from 'vue'

const isDark = ref(false)

export const useTheme = () => {
  const initTheme = () => {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('theme')
      if (saved === 'dark') {
        isDark.value = true
        document.documentElement.classList.add('dark')
      } else if (saved === 'light') {
        isDark.value = false
        document.documentElement.classList.remove('dark')
      } else {
        // Use system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        isDark.value = prefersDark
        if (prefersDark) {
          document.documentElement.classList.add('dark')
        }
      }
    }
  }

  const toggleDarkMode = () => {
    isDark.value = !isDark.value
    if (typeof window !== 'undefined') {
      if (isDark.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
      } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
      }
    }
  }

  const setDarkMode = (dark: boolean) => {
    isDark.value = dark
    if (typeof window !== 'undefined') {
      if (dark) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
      } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
      }
    }
  }

  return {
    isDark,
    initTheme,
    toggleDarkMode,
    setDarkMode,
  }
}
