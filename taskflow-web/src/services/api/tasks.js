import apiClient from './client'

/**
 * Tasks API — semua request ke /api/v1/projects/{project}/tasks dan /api/v1/tasks/{task}
 */

/**
 * @param {number} projectId
 * @param {{ status?: string, assignee_id?: number }} filters
 */
export const getTasks = (projectId, filters = {}) => {
  const params = new URLSearchParams()
  if (filters.status)      params.set('status', filters.status)
  if (filters.assignee_id) params.set('assignee_id', filters.assignee_id)

  return apiClient
    .get(`/projects/${projectId}/tasks`, { params })
    .then((r) => r.data.data)
}

export const createTask = (projectId, data) =>
  apiClient.post(`/projects/${projectId}/tasks`, data).then((r) => r.data.data)

export const getTask = (taskId) =>
  apiClient.get(`/tasks/${taskId}`).then((r) => r.data.data)

export const updateTask = ({ id, ...data }) =>
  apiClient.put(`/tasks/${id}`, data).then((r) => r.data.data)

export const deleteTask = (id) =>
  apiClient.delete(`/tasks/${id}`).then((r) => r.data)
