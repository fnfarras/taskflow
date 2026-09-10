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
const registerSchema = z
  .object({
    name: z
      .string()
      .min(1, 'Nama wajib diisi.')
      .max(255, 'Nama maksimal 255 karakter.'),
    email: z
      .string()
      .min(1, 'Email wajib diisi.')
      .email('Format email tidak valid.'),
    password: z
      .string()
      .min(8, 'Password minimal 8 karakter.'),
    password_confirmation: z
      .string()
      .min(1, 'Konfirmasi password wajib diisi.'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Konfirmasi password tidak cocok.',
    path: ['password_confirmation'],
  })

// ---------------------------------------------------------------------------
// Helper — ekstrak pesan error dari response API
// ---------------------------------------------------------------------------
const extractApiError = (err) => {
  const data = err?.response?.data

  // Error per-field dari Laravel validation
  if (data?.data?.errors) {
    const firstError = Object.values(data.data.errors).flat()[0]
    if (firstError) return firstError
  }

  return data?.message ?? 'Registrasi gagal. Silakan coba lagi.'
}

// ---------------------------------------------------------------------------
// Component
// ---------------------------------------------------------------------------
const RegisterPage = () => {
  const navigate   = useNavigate()
  const setAuth    = useAuthStore((state) => state.setAuth)
  const [apiError, setApiError] = useState(null)

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
  })

  const onSubmit = async (data) => {
    setApiError(null)
    try {
      // Register → otomatis login (response mengandung token)
      const res = await apiClient.post('/register', data)
      setAuth(res.data.data.user, res.data.data.token)
      navigate('/dashboard', { replace: true })
    } catch (err) {
      setApiError(extractApiError(err))
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-12">
      {/* Ambient background glow */}
      <div className="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
        <div className="absolute -top-60 -right-40 w-[500px] h-[500px] rounded-full bg-violet-700/15 blur-3xl" />
        <div className="absolute -bottom-60 -left-40 w-[500px] h-[500px] rounded-full bg-cyan-700/15 blur-3xl" />
      </div>

      <div className="w-full max-w-md">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold gradient-text mb-2">TaskFlow</h1>
          <p className="text-slate-400 text-sm">Buat akun dan mulai produktif</p>
        </div>

        {/* Card */}
        <div className="glass rounded-2xl p-8 shadow-2xl shadow-black/40">
          <form
            id="register-form"
            onSubmit={handleSubmit(onSubmit)}
            noValidate
            className="space-y-5"
          >
            {/* API-level error */}
            <ApiErrorAlert
              message={apiError}
              onClose={() => setApiError(null)}
            />

            {/* Name */}
            <Input
              label="Nama Lengkap"
              id="register-name"
              type="text"
              placeholder="Nama lengkap kamu"
              autoComplete="name"
              error={errors.name?.message}
              {...register('name')}
            />

            {/* Email */}
            <Input
              label="Email"
              id="register-email"
              type="email"
              placeholder="nama@email.com"
              autoComplete="email"
              error={errors.email?.message}
              {...register('email')}
            />

            {/* Password */}
            <Input
              label="Password"
              id="register-password"
              type="password"
              placeholder="Min. 8 karakter"
              autoComplete="new-password"
              error={errors.password?.message}
              {...register('password')}
            />

            {/* Password Confirmation */}
            <Input
              label="Konfirmasi Password"
              id="register-password-confirmation"
              type="password"
              placeholder="Ulangi password"
              autoComplete="new-password"
              error={errors.password_confirmation?.message}
              {...register('password_confirmation')}
            />

            {/* Submit */}
            <Button
              type="submit"
              isLoading={isSubmitting}
              fullWidth
              size="lg"
              className="mt-2"
            >
              {isSubmitting ? 'Membuat akun...' : 'Buat Akun'}
            </Button>
          </form>

          <p className="text-center text-slate-500 text-sm mt-6">
            Sudah punya akun?{' '}
            <Link
              to="/login"
              className="text-violet-400 hover:text-violet-300 font-medium transition"
            >
              Masuk di sini
            </Link>
          </p>
        </div>
      </div>
    </div>
  )
}

export default RegisterPage
