/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue"
  ],
  theme: {
    extend: {
      colors: {
        'telkom-maroon': '#B6252A',
        'telkom-red': '#ED1E28',
        'telkom-grey-dark': '#55565B',
        'telkom-grey': '#959597',
        'telkom-white': '#FFFFFF',
        'telkom-black': '#000000',
      }
    },
  },
  plugins: [],
}