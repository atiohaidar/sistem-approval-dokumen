import type { Document, CreateDocumentRequest, PaginatedResponse, PublicDocumentInfo } from '~/types/api'

export const useDocuments = () => {
  const { $api } = useNuxtApp()

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
    return response.data
  }

  const getPublicInfo = async (id: number) => {
    const response = await $api.get<PublicDocumentInfo>(`/documents/${id}/public-info`)
    return response.data
  }

  return {
    getDocuments,
    getDocument,
    createDocument,
    updateDocument,
    deleteDocument,
    downloadDocument,
    getPublicInfo,
  }
}
