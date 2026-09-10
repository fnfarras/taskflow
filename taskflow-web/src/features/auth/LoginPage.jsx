import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

import apiClient from '@/services/api/client'
import useAuthStore from '@/store/useAuthStore'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import ApiErrorAlert from '@/components/ui/ApiErrorAlert'

// ---------------------------------------------------------------------------
// Validation Schema
// ---------------------------------------------------------------------------
const loginSchema = z.object({
  email: z
    .string()
    .min(1, 'Email wajib diisi.')
    .email('Format email tidak valid.'),
  password: z
    .string()
    .min(1, 'Password wajib diisi.'),
})

// ---------------------------------------------------------------------------
// Helper — ekstrak pesan error dari response API
// ---------------------------------------------------------------------------
const extractApiError = (err) => {
  const data = err?.response?.data

  // Cek errors per-field (dari response Laravel validation)
  if (data?.data?.errors) {
    const firstField = Object.values(data.data.errors).flat()[0]
    if (firstField) return firstField
  }

  // Fallback ke message level atas
  return data?.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
}

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------
const LoginPage = () => {
  const navigate   = useNavigate()
  const setAuth    = useAuthStore((state) => state.setAuth)
  const [apiError, setApiError] = useState(null)

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  })

  const onSubmit = async (data) => {
    setApiError(null)
    try {
      const res = await apiClient.post('/login', data)
      setAuth(res.data.data.user, res.data.data.token)
      navigate('/dashboard', { replace: true })
    } catch (err) {
      setApiError(extractApiError(err))
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4">
      {/* Ambient background glow */}
      <div className="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
        <div className="absolute -top-60 -left-40 w-[500px] h-[500px] rounded-full bg-violet-700/15 blur-3xl" />
        <div className="absolute -bottom-60 -right-40 w-[500px] h-[500px] rounded-full bg-cyan-700/15 blur-3xl" />
      </div>

      <div className="w-full max-w-md">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold gradient-text mb-2">TaskFlow</h1>
          <p className="text-slate-400 text-sm">Masuk ke akun kamu untuk melanjutkan</p>
        </div>

        {/* Card */}
        <div className="glass rounded-2xl p-8 shadow-2xl shadow-black/40">
          <form
            id="login-form"
            onSubmit={handleSubmit(onSubmit)}
            noValidate
            className="space-y-5"
          >
            {/* API-level error */}
            <ApiErrorAlert
              message={apiError}
              onClose={() => setApiError(null)}
            />

            {/* Email */}
            <Input
              label="Email"
              id="login-email"
              type="email"
              placeholder="nama@email.com"
              autoComplete="email"
              error={errors.email?.message}
              {...register('email')}
            />

            {/* Password */}
            <Input
              label="Password"
              id="login-password"
              type="password"
              placeholder="••••••••"
              autoComplete="current-password"
              error={errors.password?.message}
              {...register('password')}
            />

            {/* Submit */}
            <Button
              type="submit"
              isLoading={isSubmitting}
              fullWidth
              size="lg"
              className="mt-2"
            >
              {isSubmitting ? 'Memproses...' : 'Masuk'}
            </Button>
          </form>

          <p className="text-center text-slate-500 text-sm mt-6">
            Belum punya akun?{' '}
            <Link
              to="/register"
              className="text-violet-400 hover:text-violet-300 font-medium transition"
            >
              Daftar sekarang
            </Link>
          </p>
        </div>
      </div>
    </div>
  )
}

export default LoginPage
