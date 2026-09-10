import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import Avatar from '@/components/ui/Avatar'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import ApiErrorAlert from '@/components/ui/ApiErrorAlert'

const inviteSchema = z.object({
  email: z.string().email('Format email tidak valid.').min(1, 'Email wajib diisi.'),
})

/**
 * MemberManager — section untuk owner: lihat member + invite via email.
 * Hanya ditampilkan kalau user adalah owner project.
 */
const MemberManager = ({ members = [], onAddMember, isAddingMember }) => {
  const [apiError, setApiError] = useState(null)
  const [successMsg, setSuccessMsg] = useState(null)

  const { register, handleSubmit, reset, formState: { errors } } = useForm({
    resolver: zodResolver(inviteSchema),
    defaultValues: { email: '' },
  })

  const onSubmit = async (data) => {
    setApiError(null)
    setSuccessMsg(null)
    try {
      await onAddMember({ email: data.email, role: 'member' })
      setSuccessMsg(`${data.email} berhasil ditambahkan ke project.`)
      reset()
    } catch (err) {
      const errs = err?.response?.data?.data?.errors
      setApiError(errs ? Object.values(errs).flat()[0] : err?.response?.data?.message ?? 'Gagal menambahkan member.')
    }
  }

  return (
    <aside className="glass rounded-2xl p-5 space-y-5">
      <h3 className="font-semibold text-slate-200 text-sm flex items-center gap-2">
        <svg className="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
          />
        </svg>
        Manage Members
      </h3>

      {/* Member list */}
      <ul className="space-y-2.5">
        {members.map((m) => (
          <li key={m.id ?? m.user_id} className="flex items-center gap-3">
            <Avatar name={m.user?.name ?? 'User'} size="sm" />
            <div className="flex-1 min-w-0">
              <p className="text-sm text-slate-200 truncate">{m.user?.name ?? '—'}</p>
              <p className="text-xs text-slate-500 truncate">{m.user?.email ?? '—'}</p>
            </div>
            <Badge variant="role" value={m.role} />
          </li>
        ))}
        {members.length === 0 && (
          <li className="text-xs text-slate-600 text-center py-2">Belum ada member</li>
        )}
      </ul>

      {/* Invite form */}
      <div className="border-t border-white/10 pt-4 space-y-3">
        <p className="text-xs font-medium text-slate-400">Invite Member via Email</p>

        {apiError && <ApiErrorAlert message={apiError} onClose={() => setApiError(null)} />}
        {successMsg && (
          <div className="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-sm text-emerald-400">
            ✓ {successMsg}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} noValidate className="flex flex-col gap-2">
          <Input
            id="invite-email"
            type="email"
            placeholder="email@member.com"
            error={errors.email?.message}
            {...register('email')}
          />
          <Button type="submit" isLoading={isAddingMember} size="sm" fullWidth>
            {isAddingMember ? 'Menginvite...' : 'Invite'}
          </Button>
        </form>
      </div>
    </aside>
  )
}

export default MemberManager
