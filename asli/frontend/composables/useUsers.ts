import { useQuery, useMutation, useQueryClient, type UseQueryOptions } from '@tanstack/vue-query'
import type { User } from '~/types/api'

export const useUsers = () => {
  const { $api } = useNuxtApp()
  const queryClient = useQueryClient()

  // Query Keys
  const usersKeys = {
    all: ['users'] as const,
    lists: () => [...usersKeys.all, 'list'] as const,
    details: () => [...usersKeys.all, 'detail'] as const,
    detail: (id: number) => [...usersKeys.details(), id] as const,
  }

  // API Functions
  const getUsers = async () => {
    const response = await $api.get<User[]>('/users')
    return response.data
  }

  const getUser = async (id: number) => {
    const response = await $api.get<User>(`/users/${id}`)
    return response.data
  }

  const createUser = async (data: {
    name: string
    email: string
    password: string
    role: 'admin' | 'user'
  }) => {
    const response = await $api.post<User>('/users', data)
    return response.data
  }

  const updateUser = async (id: number, data: {
    name: string
    email: string
    role: 'admin' | 'user'
  }) => {
    const response = await $api.put<User>(`/users/${id}`, data)
    return response.data
  }

  const deleteUser = async (id: number) => {
    const response = await $api.delete(`/users/${id}`)
    return response.data
  }

  const changeRole = async (id: number, role: 'admin' | 'user') => {
    const response = await $api.post(`/users/${id}/change-role`, { role })
    return response.data
  }

  // Query Hooks
  const useUsersQuery = (options?: Omit<UseQueryOptions<User[]>, 'queryKey' | 'queryFn'>) => {
    return useQuery({
      queryKey: usersKeys.lists(),
      queryFn: getUsers,
      ...options,
    })
  }

  const useUserQuery = (id: MaybeRef<number>, options?: Omit<UseQueryOptions<User>, 'queryKey' | 'queryFn'>) => {
    const idRef = computed(() => unref(id))
    
    return useQuery({
      queryKey: computed(() => usersKeys.detail(idRef.value)),
      queryFn: () => getUser(idRef.value),
      enabled: computed(() => !!idRef.value),
      ...options,
    })
  }

  // Mutation Hooks
  const useCreateUserMutation = () => {
    return useMutation({
      mutationFn: createUser,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: usersKeys.lists() })
      },
    })
  }

  const useUpdateUserMutation = () => {
    return useMutation({
      mutationFn: ({ id, data }: { 
        id: number; 
        data: { name: string; email: string; role: 'admin' | 'user' } 
      }) => updateUser(id, data),
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: usersKeys.detail(variables.id) })
        queryClient.invalidateQueries({ queryKey: usersKeys.lists() })
      },
    })
  }

  const useDeleteUserMutation = () => {
    return useMutation({
      mutationFn: deleteUser,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: usersKeys.lists() })
      },
    })
  }

  const useChangeRoleMutation = () => {
    return useMutation({
      mutationFn: ({ id, role }: { id: number; role: 'admin' | 'user' }) => changeRole(id, role),
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: usersKeys.detail(variables.id) })
        queryClient.invalidateQueries({ queryKey: usersKeys.lists() })
      },
    })
  }

  return {
    // Raw API functions (for backward compatibility)
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    changeRole,
    
    // TanStack Query hooks
    useUsersQuery,
    useUserQuery,
    useCreateUserMutation,
    useUpdateUserMutation,
    useDeleteUserMutation,
    useChangeRoleMutation,
    
    // Query keys
    usersKeys,
  }
}
