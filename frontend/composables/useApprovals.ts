import type { Document, ApprovalAction, DelegateApproval } from '~/types/api'

export const useApprovals = () => {
  const { $api } = useNuxtApp()

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

  return {
    getPendingApprovals,
    processApproval,
    delegateApproval,
  }
}
