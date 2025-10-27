import { useQuery, useMutation, useQueryClient, type UseQueryOptions } from '@tanstack/vue-query'
import type { Document, ApprovalAction, DelegateApproval } from '~/types/api'

export const useApprovals = () => {
  const { $api } = useNuxtApp()
  const queryClient = useQueryClient()

  // Query Keys
  const approvalsKeys = {
    all: ['approvals'] as const,
    pending: () => [...approvalsKeys.all, 'pending'] as const,
  }

  // API Functions
  const getPendingApprovals = async () => {
    const response = await $api.get<Document[]>('/approvals/pending')
    return response.data
  }

  const processApproval = async (documentId: number, data: ApprovalAction) => {
    const response = await $api.post(`/approvals/documents/${documentId}/process`, data)
    return response.data
  }

  const delegateApproval = async (documentId: number, data: DelegateApproval) => {
    const response = await $api.post(`/approvals/documents/${documentId}/delegate`, data)
    return response.data
  }

  // Query Hooks
  const usePendingApprovalsQuery = (options?: Omit<UseQueryOptions<Document[]>, 'queryKey' | 'queryFn'>) => {
    return useQuery({
      queryKey: approvalsKeys.pending(),
      queryFn: getPendingApprovals,
      ...options,
    })
  }

  // Mutation Hooks
  const useProcessApprovalMutation = () => {
    return useMutation({
      mutationFn: ({ documentId, data }: { documentId: number; data: ApprovalAction }) => 
        processApproval(documentId, data),
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: approvalsKeys.pending() })
        queryClient.invalidateQueries({ queryKey: ['documents'] })
      },
    })
  }

  const useDelegateApprovalMutation = () => {
    return useMutation({
      mutationFn: ({ documentId, data }: { documentId: number; data: DelegateApproval }) => 
        delegateApproval(documentId, data),
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: approvalsKeys.pending() })
        queryClient.invalidateQueries({ queryKey: ['documents'] })
      },
    })
  }

  return {
    // Raw API functions (for backward compatibility)
    getPendingApprovals,
    processApproval,
    delegateApproval,
    
    // TanStack Query hooks
    usePendingApprovalsQuery,
    useProcessApprovalMutation,
    useDelegateApprovalMutation,
    
    // Query keys
    approvalsKeys,
  }
}
