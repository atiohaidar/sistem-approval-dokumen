export default defineNuxtConfig({
  buildModules: ["@nuxtjs/style-resources"],
  devtools: { enabled: true },
  styleResources: {
    scss: ["@/assets/scss/app.scss"],
  },

  app: {
    head: {
      title: "Sistem Approval Dokumen - YPT",
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Sistem Approval Dokumen Multi Tingkat dengan Digital Signature' }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/logo.png' },
        {
          href: "https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap",
          rel: "stylesheet",
        },
      ],
    },
  },

  css: ["bootstrap/dist/css/bootstrap.css", "@/assets/scss/app.scss"],

  build: {
    loaders: {
      scss: {
        implementation: require("sass"),
      },
    },
  },

  modules: [
    '@pinia/nuxt',
  ],

  plugins: [
    "@/plugins/bootstrap.client.ts",
    "@/plugins/toast.client.ts",
    "@/plugins/api.ts",
    "@/plugins/vue-query.ts",
    "@/plugins/initAuth.client.ts",
  ],

  runtimeConfig: {
    public: {
      baseApiUrl: process.env.NUXT_BASE_API_URL || "",
      apiBase: process.env.NUXT_PUBLIC_API_BASE || "http://localhost:8000/api",
    },
  },
  compatibilityDate: "2024-07-06",
});
