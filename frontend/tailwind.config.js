/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'],
      },
      colors: {
        // YPT Brand Colors
        telkom: {
          red: '#EE3124',
          'red-dark': '#C61E1E',
          'red-light': '#F25C50',
          grey: '#6D6E71',
          'grey-dark': '#58595B',
          'grey-light': '#BCBEC0',
          blue: '#0071BC',
          'blue-light': '#5CB3E5',
        },
        primary: '#EE3124',
        secondary: '#6D6E71',
        accent: '#0071BC',
      },
    },
  },
  plugins: [],
}
