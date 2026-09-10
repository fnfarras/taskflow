import { forwardRef } from 'react'
import ErrorMessage from './ErrorMessage'

/**
 * Input — reusable form input dengan label dan inline error message.
 *
 * Props:
 * - label    : string — label teks di atas input
 * - error    : string | undefined — pesan error dari react-hook-form atau API
 * - id       : string — id untuk htmlFor label (default: name dari register())
 * - className: string — className tambahan untuk wrapper div
 * - ...rest  : semua props lain diteruskan ke <input> (termasuk {...register('field')})
 *
 * Contoh pemakaian:
 * ```jsx
 * <Input
 *   label="Email"
 *   type="email"
 *   placeholder="nama@email.com"
 *   error={errors.email?.message}
 *   {...register('email')}
 * />
 * ```
 */
const Input = forwardRef(function Input(
  { label, error, id, className = '', ...rest },
  ref,
) {
  const inputId = id ?? rest.name

  return (
    <div className={`flex flex-col gap-1.5 ${className}`}>
      {label && (
        <label
          htmlFor={inputId}
          className="text-sm font-medium text-slate-300 select-none"
        >
          {label}
        </label>
      )}

      <input
        id={inputId}
        ref={ref}
        aria-invalid={!!error}
        aria-describedby={error ? `${inputId}-error` : undefined}
        className={[
          'w-full bg-white/5 border rounded-xl px-4 py-3',
          'text-sm text-slate-100 placeholder-slate-500',
          'focus:outline-none focus:ring-2 transition duration-150',
          error
            ? 'border-red-500/60 focus:ring-red-500/40'
            : 'border-white/10 focus:ring-violet-500/50 focus:border-violet-500/50',
        ].join(' ')}
        {...rest}
      />

      {error && (
        <ErrorMessage id={`${inputId}-error`} message={error} />
      )}
    </div>
  )
})

export default Input
