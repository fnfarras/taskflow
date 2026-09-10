/**
 * Skeleton — komponen loading placeholder.
 * Gunakan sebagai wrapper atau pakai sub-komponen: Skeleton.Card, Skeleton.Text, Skeleton.Avatar
 *
 * Contoh:
 * ```jsx
 * if (isLoading) return <Skeleton.Card />
 * ```
 */
const base = 'animate-pulse rounded-lg bg-white/5'

const Skeleton = ({ className = '', style }) => (
  <div className={`${base} ${className}`} style={style} aria-hidden="true" />
)

/** Skeleton untuk project card */
Skeleton.Card = function SkeletonCard() {
  return (
    <div className="glass rounded-2xl p-5 space-y-3" aria-hidden="true">
      <Skeleton className="h-5 w-2/3" />
      <Skeleton className="h-3 w-full" />
      <Skeleton className="h-3 w-4/5" />
      <div className="flex items-center gap-2 pt-1">
        <Skeleton className="h-5 w-16 rounded-full" />
        <Skeleton className="h-5 w-20 rounded-full" />
      </div>
    </div>
  )
}

/** Skeleton untuk task card di kanban */
Skeleton.TaskCard = function SkeletonTaskCard() {
  return (
    <div className="glass rounded-xl p-3.5 space-y-2" aria-hidden="true">
      <Skeleton className="h-4 w-3/4" />
      <Skeleton className="h-3 w-1/2" />
      <div className="flex items-center gap-2 pt-1">
        <Skeleton className="h-5 w-5 rounded-full" />
        <Skeleton className="h-3 w-20" />
      </div>
    </div>
  )
}

/** Skeleton baris teks */
Skeleton.Text = function SkeletonText({ lines = 1, className = '' }) {
  return (
    <div className={`space-y-2 ${className}`} aria-hidden="true">
      {Array.from({ length: lines }).map((_, i) => (
        <Skeleton key={i} className={`h-3 ${i === lines - 1 ? 'w-3/4' : 'w-full'}`} />
      ))}
    </div>
  )
}

/** Skeleton avatar lingkaran */
Skeleton.Avatar = function SkeletonAvatar({ size = 8 }) {
  return <Skeleton className={`rounded-full`} style={{ width: `${size * 4}px`, height: `${size * 4}px` }} />
}

export default Skeleton
