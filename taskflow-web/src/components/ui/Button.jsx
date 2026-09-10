/**
 * Button — reusable button dengan loading state, variant, dan size.
 *
 * Props:
 * - isLoading : boolean — tampilkan spinner + disable saat true
 * - variant   : 'primary' | 'ghost' | 'danger' — style preset
 * - size      : 'sm' | 'md' | 'lg' — ukuran button
 * - fullWidth : boolean — width 100%
 * - children  : ReactNode — konten tombol
 * - ...rest   : props lain diteruskan ke <button>
 *
 * Contoh:
 * ```jsx
 * <Button isLoading={isSubmitting} fullWidth>
 *   Masuk
 * </Button>
 * ```
 */
const variantClasses = {
  primary:
    'bg-gradient-to-r from-violet-600 to-cyan-600 hover:from-violet-500 hover:to-cyan-500 text-white shadow-lg hover:shadow-violet-500/25',
  ghost:
    'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10 hover:text-slate-100',
  danger:
    'bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20',
}

const sizeClasses = {
  sm: 'px-4 py-2 text-sm',
  md: 'px-5 py-3 text-sm',
  lg: 'px-6 py-3.5 text-base',
}

const Button = ({
  isLoading = false,
  variant = 'primary',
  size = 'md',
  fullWidth = false,
  children,
  className = '',
  disabled,
  ...rest
}) => {
  const isDisabled = disabled || isLoading

  return (
    <button
      disabled={isDisabled}
      className={[
        'inline-flex items-center justify-center gap-2',
        'font-semibold rounded-xl transition-all duration-200',
        'active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-violet-500/50',
        'disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100',
        variantClasses[variant] ?? variantClasses.primary,
        sizeClasses[size] ?? sizeClasses.md,
        fullWidth ? 'w-full' : '',
        className,
      ].join(' ')}
      {...rest}
    >
      {isLoading && (
        <svg
          className="animate-spin h-4 w-4 shrink-0"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <circle
            className="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            strokeWidth="4"
          />
          <path
            className="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
          />
        </svg>
      )}
      {children}
    </button>
  )
}

export default Button
