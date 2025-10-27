import { useQuery, useMutation, useQueryClient, type UseQueryOptions } from '@tanstack/vue-query'
import type { Document, CreateDocumentRequest, PaginatedResponse, PublicDocumentInfo } from '~/types/api'

export const useDocuments = () => {
  const { $api } = useNuxtApp()
  const queryClient = useQueryClient()

  // Query Keys
  const documentsKeys = {
    all: ['documents'] as const,
    lists: () => [...documentsKeys.all, 'list'] as const,
    list: (params?: any) => [...documentsKeys.lists(), params] as const,
    details: () => [...documentsKeys.all, 'detail'] as const,
    detail: (id: number) => [...documentsKeys.details(), id] as const,
    publicInfo: (id: number) => [...documentsKeys.all, 'public-info', id] as const,
  }

  // API Functions
  const getDocuments = async (params?: {
    status?: string
    created_by?: number
    search?: string
    page?: number
  }) => {
    const response = await $api.get<PaginatedResponse<Document>>('/documents', { params })
    return response.data
  }

  const getDocument = async (id: number) => {
    const response = await $api.get<Document>(`/documents/${id}`)
    return response.data
  }

  const createDocument = async (data: FormData) => {
    const response = await $api.post<Document>('/documents', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data
  }

  const updateDocument = async (id: number, data: FormData) => {
    const response = await $api.post<Document>(`/documents/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      params: { _method: 'PUT' },
    })
    return response.data
  }

  const deleteDocument = async (id: number) => {
    const response = await $api.delete(`/documents/${id}`)
    return response.data
  }

  const downloadDocument = async (id: number) => {
    const response = await $api.get(`/documents/${id}/download`, {
      responseType: 'blob',
    })

    // If server returned JSON error (often as a blob when responseType='blob'),
    // parse it and throw a readable error so UI can show correct message.
    const contentType = (response.headers && (response.headers['content-type'] || response.headers['Content-Type'])) || ''
    if (contentType.includes('application/json')) {
      try {
        const text = await (response.data as Blob).text()
        const json = JSON.parse(text)
        throw new Error(json.message || 'Gagal mendownload dokumen')
      } catch (err: any) {
        // If parsing fails, throw a generic error including status
        throw new Error(err?.message || 'Gagal mendownload dokumen')
      }
    }

    return response.data
  }

  const getPublicInfo = async (id: number) => {
    const response = await $api.get<PublicDocumentInfo>(`/documents/${id}/public-info`)
    return response.data
  }

  // Query Hooks
  const useDocumentsQuery = (params?: MaybeRef<{
    status?: string
    created_by?: number
    search?: string
    page?: number
  }>, options?: Omit<UseQueryOptions<PaginatedResponse<Document>>, 'queryKey' | 'queryFn'>) => {
    const paramsRef = computed(() => unref(params))
    
    return useQuery({
      queryKey: computed(() => documentsKeys.list(paramsRef.value)),
      queryFn: () => getDocuments(paramsRef.value),
      ...options,
    })
  }

  const useDocumentQuery = (id: MaybeRef<number>, options?: Omit<UseQueryOptions<Document>, 'queryKey' | 'queryFn'>) => {
    const idRef = computed(() => unref(id))
    
    return useQuery({
      queryKey: computed(() => documentsKeys.detail(idRef.value)),
      queryFn: () => getDocument(idRef.value),
      enabled: computed(() => !!idRef.value),
      ...options,
    })
  }

  const usePublicInfoQuery = (id: MaybeRef<number>, options?: Omit<UseQueryOptions<PublicDocumentInfo>, 'queryKey' | 'queryFn'>) => {
    const idRef = computed(() => unref(id))
    
    return useQuery({
      queryKey: computed(() => documentsKeys.publicInfo(idRef.value)),
      queryFn: () => getPublicInfo(idRef.value),
      enabled: computed(() => !!idRef.value),
      ...options,
    })
  }

  // Mutation Hooks
  const useCreateDocumentMutation = () => {
    return useMutation({
      mutationFn: createDocument,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: documentsKeys.lists() })
      },
    })
  }

  const useUpdateDocumentMutation = () => {
    return useMutation({
      mutationFn: ({ id, data }: { id: number; data: FormData }) => updateDocument(id, data),
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: documentsKeys.detail(variables.id) })
        queryClient.invalidateQueries({ queryKey: documentsKeys.lists() })
      },
    })
  }

  const useDeleteDocumentMutation = () => {
    return useMutation({
      mutationFn: deleteDocument,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: documentsKeys.lists() })
      },
    })
  }

  return {
    // Raw API functions (for backward compatibility or special cases)
    getDocuments,
    getDocument,
    createDocument,
    updateDocument,
    deleteDocument,
    downloadDocument,
    getPublicInfo,
    
    // TanStack Query hooks
    useDocumentsQuery,
    useDocumentQuery,
    usePublicInfoQuery,
    useCreateDocumentMutation,
    useUpdateDocumentMutation,
    useDeleteDocumentMutation,
    
    // Query keys (for manual invalidation if needed)
    documentsKeys,
  }
}
