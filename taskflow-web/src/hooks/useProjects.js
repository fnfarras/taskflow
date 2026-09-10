import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createProject, getProjects } from '@/services/api/projects'

export const PROJECTS_KEY = ['projects']

/**
 * useProjects — fetch semua project user + mutation create.
 *
 * Contoh:
 * ```jsx
 * const { projects, isLoading, error, createProject } = useProjects()
 * ```
 */
export const useProjects = () => {
  const queryClient = useQueryClient()

  const query = useQuery({
    queryKey: PROJECTS_KEY,
    queryFn: getProjects,
  })

  const mutation = useMutation({
    mutationFn: createProject,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: PROJECTS_KEY }),
  })

  return {
    projects: query.data ?? [],
    isLoading: query.isLoading,
    error: query.error,
    createProject: mutation.mutateAsync,
    isCreating: mutation.isPending,
  }
}
