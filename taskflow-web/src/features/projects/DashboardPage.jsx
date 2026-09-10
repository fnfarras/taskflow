import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import apiClient from '@/services/api/client'
import useAuthStore from '@/store/useAuthStore'
import { useProjects } from '@/hooks/useProjects'
import ProjectCard from './ProjectCard'
import CreateProjectModal from './CreateProjectModal'
import Skeleton from '@/components/ui/Skeleton'
import Button from '@/components/ui/Button'

const DashboardPage = () => {
  const navigate = useNavigate()
  const { user, clearAuth } = useAuthStore()
  const { projects, isLoading, error, createProject, isCreating } = useProjects()
  const [showModal, setShowModal] = useState(false)

  const handleLogout = async () => {
    try { await apiClient.post('/logout') } catch { /* ignore */ }
    clearAuth()
    navigate('/login', { replace: true })
  }

  return (
    <div className="min-h-screen">
      {/* Navbar */}
      <nav className="glass border-b border-white/10 px-6 py-4 sticky top-0 z-40">
        <div className="max-w-6xl mx-auto flex items-center justify-between">
          <h1 className="text-xl font-bold gradient-text">TaskFlow</h1>
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-cyan-500 flex items-center justify-center text-sm font-bold select-none">
                {user?.name?.charAt(0).toUpperCase()}
              </div>
              <span className="text-sm text-slate-300 hidden sm:block">{user?.name}</span>
            </div>
            <button
              onClick={handleLogout}
              className="text-sm text-slate-400 hover:text-red-400 transition px-3 py-1.5 rounded-lg hover:bg-red-500/10"
            >
              Keluar
            </button>
          </div>
        </div>
      </nav>

      {/* Main */}
      <main className="max-w-6xl mx-auto px-6 py-10">
        {/* Header row */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
          <div>
            <h2 className="text-2xl font-bold text-slate-100">
              Selamat datang, <span className="gradient-text">{user?.name?.split(' ')[0]}</span> 👋
            </h2>
            <p className="text-slate-400 text-sm mt-1">
              {isLoading ? '…' : `${projects.length} project aktif`}
            </p>
          </div>
          <Button onClick={() => setShowModal(true)} size="md">
            + Buat Project
          </Button>
        </div>

        {/* Error state */}
        {error && !isLoading && (
          <div className="glass rounded-2xl p-8 text-center border-red-500/30">
            <p className="text-red-400 text-sm">Gagal memuat project: {error.message}</p>
          </div>
        )}

        {/* Loading skeleton */}
        {isLoading && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {[1, 2, 3].map((i) => <Skeleton.Card key={i} />)}
          </div>
        )}

        {/* Empty state */}
        {!isLoading && !error && projects.length === 0 && (
          <div className="glass rounded-2xl p-16 text-center">
            <div className="text-5xl mb-4">📁</div>
            <h3 className="text-lg font-semibold text-slate-200 mb-2">Belum ada project</h3>
            <p className="text-slate-400 text-sm mb-6">
              Buat project pertama kamu dan mulai mengelola task bersama tim.
            </p>
            <Button onClick={() => setShowModal(true)}>
              + Buat Project Pertama
            </Button>
          </div>
        )}

        {/* Project grid */}
        {!isLoading && projects.length > 0 && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {projects.map((project) => (
              <ProjectCard key={project.id} project={project} />
            ))}
          </div>
        )}
      </main>

      {/* Create Project Modal */}
      <CreateProjectModal
        isOpen={showModal}
        onClose={() => setShowModal(false)}
        onCreate={createProject}
      />
    </div>
  )
}

export default DashboardPage
