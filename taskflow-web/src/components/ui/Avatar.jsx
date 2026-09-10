/**
 * Avatar — avatar dengan inisial nama dan gradient background.
 *
 * Props:
 * - name  : string — nama user (diambil huruf pertama)
 * - size  : 'xs' | 'sm' | 'md' | 'lg'
 * - title : string — tooltip (default: name)
 */

const SIZE_CLASSES = {
  xs: 'w-5 h-5 text-[10px]',
  sm: 'w-7 h-7 text-xs',
  md: 'w-9 h-9 text-sm',
  lg: 'w-12 h-12 text-base',
}

// Generate warna konsisten berdasarkan nama (deterministic)
const GRADIENTS = [
  'from-violet-500 to-purple-600',
  'from-cyan-500 to-blue-600',
  'from-emerald-500 to-teal-600',
  'from-rose-500 to-pink-600',
  'from-amber-500 to-orange-600',
  'from-indigo-500 to-violet-600',
]

const getGradient = (name = '') => {
  const code = name.split('').reduce((acc, c) => acc + c.charCodeAt(0), 0)
  return GRADIENTS[code % GRADIENTS.length]
}

const Avatar = ({ name = '?', size = 'md', title, className = '' }) => {
  const initials = name
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')

  return (
    <span
      title={title ?? name}
      className={[
        'inline-flex items-center justify-center rounded-full font-bold select-none',
        'bg-gradient-to-br text-white shrink-0',
        getGradient(name),
        SIZE_CLASSES[size] ?? SIZE_CLASSES.md,
        className,
      ].join(' ')}
    >
      {initials || '?'}
    </span>
  )
}

export default Avatar
