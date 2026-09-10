import apiClient from './client'

/**
 * Projects API — semua request ke /api/v1/projects
 */

export const getProjects = () =>
  apiClient.get('/projects').then((r) => r.data.data)

export const createProject = (data) =>
  apiClient.post('/projects', data).then((r) => r.data.data)

export const getProject = (id) =>
  apiClient.get(`/projects/${id}`).then((r) => r.data.data)

export const updateProject = ({ id, ...data }) =>
  apiClient.put(`/projects/${id}`, data).then((r) => r.data.data)

export const deleteProject = (id) =>
  apiClient.delete(`/projects/${id}`).then((r) => r.data)

/**
 * Tambah member ke project.
 * Bisa resolve by email atau user_id.
 * @param {number} projectId
 * @param {{ email?: string, user_id?: number, role?: string }} data
 */
export const addMember = (projectId, data) =>
  apiClient.post(`/projects/${projectId}/members`, data).then((r) => r.data)
