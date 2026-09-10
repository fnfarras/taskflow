/**
 * ErrorMessage — pesan error inline untuk field form.
 *
 * Props:
 * - message : string | undefined — pesan yang ditampilkan
 * - id      : string | undefined — untuk aria-describedby
 *
 * Jika message kosong/undefined, komponen tidak render apapun.
 */
const ErrorMessage = ({ message, id }) => {
  if (!message) return null

  return (
    <p
      id={id}
      role="alert"
      className="flex items-center gap-1.5 text-xs text-red-400 mt-0.5"
    >
      {/* Icon seru kecil */}
      <svg
        className="w-3.5 h-3.5 shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
        aria-hidden="true"
      >
        <path
          fillRule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clipRule="evenodd"
        />
      </svg>
      {message}
    </p>
  )
}

export default ErrorMessage
