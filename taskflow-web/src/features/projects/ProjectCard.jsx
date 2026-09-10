import { Link } from 'react-router-dom'
import Badge from '@/components/ui/Badge'
import useAuthStore from '@/store/useAuthStore'

/**
 * ProjectCard — card untuk satu project di dashboard.
 * Klik untuk navigasi ke /projects/:id.
 */
const ProjectCard = ({ project }) => {
  const user = useAuthStore((s) => s.user)
  const isOwner = project.owner?.id === user?.id

  return (
    <Link
      to={`/projects/${project.id}`}
      className="group glass rounded-2xl p-5 flex flex-col gap-3 hover:border-violet-500/40 hover:bg-white/[0.07] transition-all duration-200 cursor-pointer"
    >
      {/* Header */}
      <div className="flex items-start justify-between gap-2">
        <h3 className="font-semibold text-slate-100 group-hover:text-violet-300 transition line-clamp-1">
          {project.name}
        </h3>
        <Badge variant="role" value={isOwner ? 'owner' : 'member'} />
      </div>

      {/* Description */}
      {project.description ? (
        <p className="text-slate-400 text-sm line-clamp-2 flex-1">{project.description}</p>
      ) : (
        <p className="text-slate-600 text-sm italic flex-1">Tidak ada deskripsi</p>
      )}

      {/* Footer */}
      <div className="flex items-center gap-3 pt-1 text-xs text-slate-500 border-t border-white/5">
        {/* Owner info */}
        <span className="flex items-center gap-1">
          <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          {project.owner?.name ?? '—'}
        </span>

        {/* Arrow icon */}
        <svg className="w-4 h-4 ml-auto text-slate-600 group-hover:text-violet-400 group-hover:translate-x-0.5 transition-all"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
        </svg>
      </div>
    </Link>
  )
}

export default ProjectCard
