import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import ProtectedRoute from '@/components/ui/ProtectedRoute'
import LoginPage from '@/features/auth/LoginPage'
import RegisterPage from '@/features/auth/RegisterPage'
import DashboardPage from '@/features/projects/DashboardPage'
import ProjectDetailPage from '@/features/projects/ProjectDetailPage'

/**
 * App — root component dengan konfigurasi routing.
 *
 * Route structure:
 *   /                   → redirect ke /dashboard
 *   /login              → LoginPage (public)
 *   /register           → RegisterPage (public)
 *   /dashboard          → DashboardPage (protected)
 *   /projects/:id       → ProjectDetailPage (protected)
 */
const App = () => {
  return (
    <BrowserRouter>
      <Routes>
        {/* Redirect root ke dashboard */}
        <Route path="/" element={<Navigate to="/dashboard" replace />} />

        {/* Public routes */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />

        {/* Protected routes */}
        <Route element={<ProtectedRoute />}>
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/projects/:id" element={<ProjectDetailPage />} />
        </Route>

        {/* Fallback 404 */}
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
