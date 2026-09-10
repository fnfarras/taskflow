import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  addMember,
  deleteProject,
  getProject,
  updateProject,
} from '@/services/api/projects'
import { PROJECTS_KEY } from './useProjects'

export const projectKey = (id) => ['project', String(id)]

/**
 * useProject — fetch detail satu project + mutations update, delete, addMember.
 *
 * Contoh:
 * ```jsx
 * const { project, isLoading, updateProject, deleteProject, addMember } = useProject(id)
 * ```
 */
export const useProject = (id) => {
  const queryClient = useQueryClient()
  const key = projectKey(id)

  const query = useQuery({
    queryKey: key,
    queryFn: () => getProject(id),
    enabled: !!id,
  })

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: key })
    queryClient.invalidateQueries({ queryKey: PROJECTS_KEY })
  }

  const updateMutation = useMutation({
    mutationFn: updateProject,
    onSuccess: invalidate,
  })

  const deleteMutation = useMutation({
    mutationFn: deleteProject,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: PROJECTS_KEY }),
  })

  const addMemberMutation = useMutation({
    mutationFn: ({ data }) => addMember(id, data),
    onSuccess: invalidate,
  })

  return {
    project: query.data ?? null,
    isLoading: query.isLoading,
    error: query.error,
    updateProject: updateMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    deleteProject: deleteMutation.mutateAsync,
    isDeleting: deleteMutation.isPending,
    addMember: (data) => addMemberMutation.mutateAsync({ data }),
    isAddingMember: addMemberMutation.isPending,
  }
}
