import { ref } from 'vue'
import type { Ref } from 'vue'

interface AccessToken {
  id: number
  generated_by: {
    id: number
    name: string
    email: string
  }
  expires_at: string
  access_count: number
  last_accessed_at: string | null
  metadata: Record<string, any>
  created_at: string
}

interface TokenGenerateParams {
  expires_in_hours?: number
  purpose?: string
  generated_for?: string
}

interface AccessLog {
  id: number
  document_id: number
  access_token_id: number | null
  user_id: number | null
  action: string
  ip_address: string
  user_agent: string
  success: boolean
  failure_reason: string | null
  created_at: string
  user?: {
    id: number
    name: string
    email: string
  }
}

interface AccessStats {
  total_accesses: number
  successful_accesses: number
  failed_accesses: number
  unique_users: number
  unique_ips: number
  actions: Record<string, number>
}

interface AccessLogsResponse {
  stats: AccessStats
  logs: {
    data: AccessLog[]
    per_page: number
    current_page: number
    total: number
  }
}

export const useDocumentTokens = () => {
  const { $api } = useNuxtApp()
  const authStore = useAuthStore()

  const tokens: Ref<AccessToken[]> = ref([])
  const accessLogs: Ref<AccessLog[]> = ref([])
  const accessStats: Ref<AccessStats | null> = ref(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Generate a new access token for a document
   */
  const generateToken = async (
    documentId: number,
    params: TokenGenerateParams = {}
  ): Promise<{ token_id: number; access_url: string; expires_at: string } | null> => {
    loading.value = true
    error.value = null

    try {
      const response = await $api.post(`/documents/${documentId}/access-tokens`, params)
      return response.data
    } catch (err: any) {
      error.value = err.message
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Get all active tokens for a document
   */
  const fetchTokens = async (documentId: number): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const response = await $api.get(`/documents/${documentId}/access-tokens`)
      tokens.value = response.data.tokens || []
    } catch (err: any) {
      error.value = err.message
      tokens.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Revoke a token
   */
  const revokeToken = async (
    documentId: number,
    tokenId: number,
    reason?: string
  ): Promise<boolean> => {
    loading.value = true
    error.value = null

    try {
      await $api.post(
        `/documents/${documentId}/access-tokens/${tokenId}/revoke`,
        { reason }
      )

      return true
    } catch (err: any) {
      error.value = err.message
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Rotate a token (generate new and revoke old)
   */
  const rotateToken = async (
    documentId: number,
    tokenId: number,
    expiresInHours?: number
  ): Promise<{ token_id: number; access_url: string; expires_at: string } | null> => {
    loading.value = true
    error.value = null

    try {
      const response = await $api.post(
        `/documents/${documentId}/access-tokens/${tokenId}/rotate`,
        { expires_in_hours: expiresInHours }
      )

      return response.data
    } catch (err: any) {
      error.value = err.message
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch access logs for a document
   */
  const fetchAccessLogs = async (
    documentId: number,
    hours: number = 24
  ): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const response = await $api.get(`/documents/${documentId}/access-logs`, {
        params: { hours },
      })

      const data: AccessLogsResponse = response.data
      accessStats.value = data.stats
      accessLogs.value = data.logs.data
    } catch (err: any) {
      error.value = err.message
      accessStats.value = null
      accessLogs.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Copy token URL to clipboard
   */
  const copyTokenUrl = async (url: string): Promise<boolean> => {
    try {
      await navigator.clipboard.writeText(url)
      return true
    } catch (err) {
      // Fallback for older browsers
      const textArea = document.createElement('textarea')
      textArea.value = url
      textArea.style.position = 'fixed'
      textArea.style.left = '-999999px'
      document.body.appendChild(textArea)
      textArea.select()

      try {
        const success = document.execCommand('copy')
        return success
      } catch (execError) {
        return false
      } finally {
        document.body.removeChild(textArea)
      }
    }
  }

  /**
   * Format expiration time as relative time
   */
  const formatExpiresIn = (expiresAt: string): string => {
    const now = new Date()
    const expires = new Date(expiresAt)
    const diff = expires.getTime() - now.getTime()

    if (diff < 0) return 'Kedaluwarsa'

    const hours = Math.floor(diff / (1000 * 60 * 60))
    const days = Math.floor(hours / 24)

    if (days > 0) {
      return `${days} hari lagi`
    } else if (hours > 0) {
      return `${hours} jam lagi`
    } else {
      const minutes = Math.floor(diff / (1000 * 60))
      return `${minutes} menit lagi`
    }
  }

  return {
    tokens,
    accessLogs,
    accessStats,
    loading,
    error,
    generateToken,
    fetchTokens,
    revokeToken,
    rotateToken,
    fetchAccessLogs,
    copyTokenUrl,
    formatExpiresIn,
  }
}
