import Avatar from '@/components/ui/Avatar'
import Badge from '@/components/ui/Badge'

/**
 * TaskCard — card task dalam kolom kanban.
 * Klik untuk membuka modal edit.
 */
const TaskCard = ({ task, onClick }) => {
  const isOverdue =
    task.due_date && new Date(task.due_date) < new Date() && task.status !== 'done'

  return (
    <div
      role="button"
      tabIndex={0}
      onClick={onClick}
      onKeyDown={(e) => e.key === 'Enter' && onClick()}
      className="glass rounded-xl p-3.5 space-y-2.5 cursor-pointer hover:border-violet-500/40 hover:bg-white/[0.08] transition-all duration-150 group"
    >
      {/* Title */}
      <p className="text-sm font-medium text-slate-100 leading-snug group-hover:text-violet-200 transition line-clamp-2">
        {task.title}
      </p>

      {/* Priority badge */}
      <Badge variant="priority" value={task.priority} />

      {/* Footer: assignee + due date */}
      <div className="flex items-center justify-between pt-0.5">
        {task.assignee ? (
          <div className="flex items-center gap-1.5">
            <Avatar name={task.assignee.name} size="xs" />
            <span className="text-xs text-slate-400 truncate max-w-[80px]">
              {task.assignee.name.split(' ')[0]}
            </span>
          </div>
        ) : (
          <span className="text-xs text-slate-600 italic">Unassigned</span>
        )}

        {task.due_date && (
          <span
            className={`text-xs ${isOverdue ? 'text-red-400 font-medium' : 'text-slate-500'}`}
          >
            {new Date(task.due_date).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'short',
            })}
            {isOverdue && ' ⚠'}
          </span>
        )}
      </div>
    </div>
  )
}

export default TaskCard
