/**
 * Badge — badge warna untuk status dan priority task/project.
 *
 * Props:
 * - variant : 'status' | 'priority' | 'role'
 * - value   : string
 *
 * Contoh:
 * ```jsx
 * <Badge variant="priority" value="high" />
 * <Badge variant="status" value="in_progress" />
 * <Badge variant="role" value="owner" />
 * ```
 */

const STATUS_STYLES = {
  todo:        'bg-slate-500/20 text-slate-300 border-slate-500/30',
  in_progress: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
  done:        'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
}

const STATUS_LABELS = {
  todo:        'To Do',
  in_progress: 'In Progress',
  done:        'Done',
}

const PRIORITY_STYLES = {
  low:    'bg-slate-500/20 text-slate-300 border-slate-500/30',
  medium: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  high:   'bg-red-500/20 text-red-300 border-red-500/30',
}

const PRIORITY_LABELS = {
  low:    '↓ Low',
  medium: '→ Medium',
  high:   '↑ High',
}

const ROLE_STYLES = {
  owner:  'bg-violet-500/20 text-violet-300 border-violet-500/30',
  member: 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
}

const Badge = ({ variant = 'status', value, className = '' }) => {
  let style, label

  if (variant === 'status') {
    style = STATUS_STYLES[value] ?? STATUS_STYLES.todo
    label = STATUS_LABELS[value] ?? value
  } else if (variant === 'priority') {
    style = PRIORITY_STYLES[value] ?? PRIORITY_STYLES.medium
    label = PRIORITY_LABELS[value] ?? value
  } else if (variant === 'role') {
    style = ROLE_STYLES[value] ?? ROLE_STYLES.member
    label = value === 'owner' ? '👑 Owner' : 'Member'
  }

  return (
    <span
      className={[
        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border',
        style,
        className,
      ].join(' ')}
    >
      {label}
    </span>
  )
}

export default Badge
