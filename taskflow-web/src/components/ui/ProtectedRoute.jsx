import { Navigate, Outlet } from 'react-router-dom'
import useAuthStore from '@/store/useAuthStore'

/**
 * ProtectedRoute — wrapper untuk route yang membutuhkan autentikasi.
 *
 * Cara pakai di router:
 * ```jsx
 * <Route element={<ProtectedRoute />}>
 *   <Route path="/dashboard" element={<DashboardPage />} />
 * </Route>
 * ```
 *
 * Jika user belum login (token tidak ada di store), redirect ke /login.
 * `replace` digunakan agar /login tidak masuk ke browser history.
 */
const ProtectedRoute = () => {
  const token = useAuthStore((state) => state.token)

  if (!token) {
    return <Navigate to="/login" replace />
  }

  return <Outlet />
}

export default ProtectedRoute
