import axios from 'axios'
import useAuthStore from '@/store/useAuthStore'

/**
 * Axios instance terpusat untuk semua request ke TaskFlow API.
 *
 * - baseURL diambil dari environment variable VITE_API_BASE_URL
 * - Request interceptor: otomatis attach Authorization Bearer token dari Zustand store
 * - Response interceptor: handle 401 global (auto logout jika token expired/invalid)
 */
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// -------------------------------------------------------------------
// Request Interceptor — attach token ke setiap request
// -------------------------------------------------------------------
apiClient.interceptors.request.use(
  (config) => {
    // Ambil token langsung dari store state (bukan hook) agar bisa dipakai di luar React
    const token = useAuthStore.getState().token

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => Promise.reject(error),
)

// -------------------------------------------------------------------
// Response Interceptor — handle error global
// -------------------------------------------------------------------
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    // 401 Unauthorized — token tidak valid atau expired → auto logout
    if (status === 401) {
      useAuthStore.getState().clearAuth()
      // Redirect ke login jika bukan sudah di halaman auth
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login'
      }
    }

    return Promise.reject(error)
  },
)

export default apiClient
