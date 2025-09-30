// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  
  // App configuration
  app: {
    head: {
      title: 'Sistem Approval Dokumen',
      meta: [
        { name: 'description', content: 'Sistem approval dokumen digital dengan tanda tangan QR Code' }
      ]
    }
  },

  // CSS framework - remove the css line since we'll let tailwind handle it
  
  // Modules
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt'
  ],

  // Runtime config
  runtimeConfig: {
    // Private keys (only available on server-side)
    
    // Public keys (exposed to client-side)
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost:8000/api',
      appName: 'Sistem Approval Dokumen'
    }
  },

  // Server configuration
  nitro: {
    cors: {
      origin: ['http://localhost:8000'],
      credentials: true
    }
  }
})
