import { useNavigate, useParams, Link } from 'react-router-dom'
import useAuthStore from '@/store/useAuthStore'
import { useProject } from '@/hooks/useProject'
import { useTasks } from '@/hooks/useTasks'
import KanbanBoard from '@/features/tasks/KanbanBoard'
import MemberManager from '@/features/tasks/MemberManager'
import Skeleton from '@/components/ui/Skeleton'
import Badge from '@/components/ui/Badge'

const ProjectDetailPage = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const user = useAuthStore((s) => s.user)

  const {
    project,
    isLoading: projectLoading,
    error: projectError,
    addMember,
    isAddingMember,
  } = useProject(id)

  const {
    tasks,
    isLoading: tasksLoading,
    createTask,
    updateTask,
    deleteTask,
  } = useTasks(id)

  const isOwner = project?.owner?.id === user?.id
  const members = project?.members ?? []

  // ---------------------------------------------------------------------------
  // Error state
  // ---------------------------------------------------------------------------
  if (projectError) {
    const status = projectError?.response?.status
    return (
      <div className="min-h-screen flex items-center justify-center px-4">
        <div className="glass rounded-2xl p-10 text-center max-w-md w-full">
          <div className="text-5xl mb-4">{status === 403 ? '🔒' : '⚠️'}</div>
          <h2 className="text-lg font-semibold text-slate-200 mb-2">
            {status === 403 ? 'Akses Ditolak' : 'Project Tidak Ditemukan'}
          </h2>
          <p className="text-slate-400 text-sm mb-6">
            {status === 403
              ? 'Kamu bukan member project ini. Minta owner untuk menambahkanmu.'
              : 'Project tidak ditemukan atau sudah dihapus.'}
          </p>
          <Link
            to="/dashboard"
            className="text-violet-400 hover:text-violet-300 text-sm font-medium transition"
          >
            ← Kembali ke Dashboard
          </Link>
        </div>
      </div>
    )
  }

  // ---------------------------------------------------------------------------
  // Render
  // ---------------------------------------------------------------------------
  return (
    <div className="min-h-screen">
      {/* Ambient glow */}
      <div className="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
        <div className="absolute -top-60 -left-40 w-[500px] h-[500px] rounded-full bg-violet-700/10 blur-3xl" />
      </div>

      {/* Navbar */}
      <nav className="glass border-b border-white/10 px-6 py-4 sticky top-0 z-40">
        <div className="max-w-6xl mx-auto flex items-center gap-3">
          <Link to="/dashboard" className="text-slate-400 hover:text-slate-100 transition p-1 rounded-lg hover:bg-white/10">
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <span className="text-slate-600">/</span>
          {projectLoading ? (
            <Skeleton className="h-5 w-32" />
          ) : (
            <h1 className="text-base font-semibold text-slate-100 truncate">{project?.name}</h1>
          )}
          {project && isOwner && (
            <Badge variant="role" value="owner" className="ml-1" />
          )}
        </div>
      </nav>

      {/* Main layout */}
      <main className="max-w-6xl mx-auto px-6 py-8">
        {projectLoading ? (
          // Project header skeleton
          <div className="mb-8 space-y-2">
            <Skeleton className="h-7 w-64" />
            <Skeleton.Text lines={2} className="max-w-lg" />
          </div>
        ) : (
          // Project header
          <div className="mb-8">
            <h2 className="text-2xl font-bold text-slate-100 mb-1">{project?.name}</h2>
            {project?.description && (
              <p className="text-slate-400 text-sm max-w-2xl">{project.description}</p>
            )}
          </div>
        )}

        {/* Two-column: board + sidebar */}
        <div className="flex flex-col lg:flex-row gap-6">
          {/* Kanban board — main content */}
          <div className="flex-1 min-w-0">
            <KanbanBoard
              tasks={tasks}
              isLoading={tasksLoading}
              members={members}
              onCreateTask={createTask}
              onUpdateTask={updateTask}
              onDeleteTask={deleteTask}
            />
          </div>

          {/* Sidebar — hanya untuk owner */}
          {isOwner && (
            <div className="lg:w-72 shrink-0">
              <MemberManager
                members={members}
                onAddMember={addMember}
                isAddingMember={isAddingMember}
              />
            </div>
          )}
        </div>
      </main>
    </div>
  )
}

export default ProjectDetailPage
