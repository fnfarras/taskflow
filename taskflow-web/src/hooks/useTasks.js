import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createTask, deleteTask, getTasks, updateTask } from '@/services/api/tasks'

export const tasksKey = (projectId) => ['tasks', String(projectId)]

/**
 * useTasks — fetch semua task dalam project + mutations create, update, delete.
 * Otomatis refetch setelah setiap mutasi berhasil.
 *
 * Contoh:
 * ```jsx
 * const { tasks, createTask, updateTask, deleteTask } = useTasks(projectId)
 * ```
 */
export const useTasks = (projectId) => {
  const queryClient = useQueryClient()
  const key = tasksKey(projectId)

  const query = useQuery({
    queryKey: key,
    queryFn: () => getTasks(projectId),
    enabled: !!projectId,
  })

  const invalidate = () => queryClient.invalidateQueries({ queryKey: key })

  const createMutation = useMutation({
    mutationFn: (data) => createTask(projectId, data),
    onSuccess: invalidate,
  })

  const updateMutation = useMutation({
    mutationFn: updateTask,
    // Optimistic: langsung update cache sebelum server response
    onMutate: async (updated) => {
      await queryClient.cancelQueries({ queryKey: key })
      const previous = queryClient.getQueryData(key)

      queryClient.setQueryData(key, (old = []) =>
        old.map((t) => (t.id === updated.id ? { ...t, ...updated } : t)),
      )

      return { previous }
    },
    onError: (_err, _vars, context) => {
      // Rollback jika gagal
      if (context?.previous) {
        queryClient.setQueryData(key, context.previous)
      }
    },
    onSettled: invalidate,
  })

  const deleteMutation = useMutation({
    mutationFn: deleteTask,
    onSuccess: invalidate,
  })

  return {
    tasks: query.data ?? [],
    isLoading: query.isLoading,
    error: query.error,
    createTask: createMutation.mutateAsync,
    isCreating: createMutation.isPending,
    updateTask: updateMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    deleteTask: deleteMutation.mutateAsync,
    isDeleting: deleteMutation.isPending,
  }
}
