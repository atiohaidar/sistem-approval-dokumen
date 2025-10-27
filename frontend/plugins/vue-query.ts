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
  }

  nuxtApp.vueApp.use(VueQueryPlugin, options)

  return {
    provide: {
      queryClient,
    },
  }
})
