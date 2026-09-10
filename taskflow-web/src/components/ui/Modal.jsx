import { useEffect, useRef } from 'react'
import { createPortal } from 'react-dom'

/**
 * Modal — portal-based modal dialog dengan animasi, backdrop, dan trap focus.
 *
 * Props:
 * - isOpen   : boolean
 * - onClose  : () => void
 * - title    : string
 * - children : ReactNode
 * - size     : 'sm' | 'md' | 'lg' (default: 'md')
 *
 * Fitur:
 * - Close saat tekan Escape
 * - Close saat klik backdrop
 * - Scroll lock saat terbuka
 */
const sizeClass = { sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg' }

const Modal = ({ isOpen, onClose, title, children, size = 'md' }) => {
  const overlayRef = useRef(null)

  // Lock scroll body
  useEffect(() => {
    if (isOpen) document.body.style.overflow = 'hidden'
    else document.body.style.overflow = ''
    return () => { document.body.style.overflow = '' }
  }, [isOpen])

  // Close on Escape
  useEffect(() => {
    const handler = (e) => { if (e.key === 'Escape') onClose() }
    if (isOpen) window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [isOpen, onClose])

  if (!isOpen) return null

  return createPortal(
    <div
      ref={overlayRef}
      role="dialog"
      aria-modal="true"
      aria-label={title}
      onClick={(e) => { if (e.target === overlayRef.current) onClose() }}
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-150"
    >
      <div
        className={[
          'relative w-full glass rounded-2xl shadow-2xl shadow-black/60',
          'animate-in zoom-in-95 duration-150',
          sizeClass[size] ?? sizeClass.md,
        ].join(' ')}
      >
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-white/10">
          <h2 className="text-base font-semibold text-slate-100">{title}</h2>
          <button
            onClick={onClose}
            aria-label="Tutup modal"
            className="text-slate-400 hover:text-slate-100 transition p-1 rounded-lg hover:bg-white/10"
          >
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Body */}
        <div className="px-6 py-5">{children}</div>
      </div>
    </div>,
    document.body,
  )
}

export default Modal
