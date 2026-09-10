import { create } from 'zustand'
import { persist } from 'zustand/middleware'

/**
 * Auth Store — menyimpan user dan token di localStorage via persist middleware.
 *
 * Dipakai oleh:
 * - axios interceptor (src/services/api/client.js) untuk attach Bearer token
 * - ProtectedRoute untuk cek status login
 * - Komponen UI untuk akses data user
 */
const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      token: null,

      /** Set user dan token setelah login/register berhasil */
      setAuth: (user, token) => set({ user, token }),

      /** Hapus state auth saat logout */
      clearAuth: () => set({ user: null, token: null }),
    }),
    {
      name: 'taskflow-auth', // key di localStorage
      partialize: (state) => ({ user: state.user, token: state.token }),
    },
  ),
)

export default useAuthStore
