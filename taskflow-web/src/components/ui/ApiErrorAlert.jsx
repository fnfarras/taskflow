/**
 * ApiErrorAlert — menampilkan error level API (bukan field error).
 * Dipakai untuk pesan seperti "Email sudah terdaftar" atau "Server error".
 *
 * Props:
 * - message : string | null — pesan dari API response
 * - onClose : function | undefined — callback untuk dismiss (opsional)
 */
const ApiErrorAlert = ({ message, onClose }) => {
  if (!message) return null

  return (
    <div
      role="alert"
      className="flex items-start gap-3 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-sm text-red-400 animate-in fade-in duration-200"
    >
      {/* Icon */}
      <svg
        className="w-4 h-4 mt-0.5 shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
        aria-hidden="true"
      >
        <path
          fillRule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
          clipRule="evenodd"
        />
      </svg>

      <p className="flex-1 leading-relaxed">{message}</p>

      {/* Tombol dismiss (opsional) */}
      {onClose && (
        <button
          onClick={onClose}
          aria-label="Tutup notifikasi"
          className="text-red-400/60 hover:text-red-400 transition shrink-0 -mt-0.5"
        >
          <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              fillRule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clipRule="evenodd"
            />
          </svg>
        </button>
      )}
    </div>
  )
}

export default ApiErrorAlert
