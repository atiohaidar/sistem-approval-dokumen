import type { User } from '~/types/api'

export const useUsers = () => {
  const { $api } = useNuxtApp()

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

  return {
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    changeRole,
  }
}
