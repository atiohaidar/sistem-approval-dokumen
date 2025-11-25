import { VueQueryPlugin, QueryClient, type VueQueryPluginOptions } from '@tanstack/vue-query'

export default defineNuxtPlugin((nuxtApp) => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 1000 * 60 * 5, // 5 minutes
        refetchOnWindowFocus: true,
        refetchOnReconnect: true,
        retry: 1,
        retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
      },
      mutations: {
        retry: 1,
      },
    },
  })

  const options: VueQueryPluginOptions = {
    queryClient,
    enableDevtoolsV6Plugin: true,
  }

  nuxtApp.vueApp.use(VueQueryPlugin, options)

  // Add devtools in development
  if (process.dev && typeof window !== 'undefined') {
    import('@tanstack/vue-query-devtools').then(({ VueQueryDevtools }) => {
      nuxtApp.vueApp.component('VueQueryDevtools', VueQueryDevtools)
    })
  }

  return {
    provide: {
      queryClient,
    },
  }
})
