import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import ApiErrorAlert from '@/components/ui/ApiErrorAlert'

const schema = z.object({
  name:        z.string().min(1, 'Nama project wajib diisi.').max(255),
  description: z.string().optional(),
})

const CreateProjectModal = ({ isOpen, onClose, onCreate }) => {
  const [apiError, setApiError] = useState(null)

  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(schema),
    defaultValues: { name: '', description: '' },
  })

  const onSubmit = async (data) => {
    setApiError(null)
    try {
      await onCreate(data)
      reset()
      onClose()
    } catch (err) {
      const msg = err?.response?.data?.data?.errors
        ? Object.values(err.response.data.data.errors).flat()[0]
        : err?.response?.data?.message ?? 'Gagal membuat project.'
      setApiError(msg)
    }
  }

  const handleClose = () => { reset(); setApiError(null); onClose() }

  return (
    <Modal isOpen={isOpen} onClose={handleClose} title="Buat Project Baru">
      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
        <ApiErrorAlert message={apiError} onClose={() => setApiError(null)} />

        <Input
          label="Nama Project"
          id="project-name"
          placeholder="Nama project kamu"
          error={errors.name?.message}
          {...register('name')}
        />

        <div className="flex flex-col gap-1.5">
          <label className="text-sm font-medium text-slate-300">
            Deskripsi <span className="text-slate-500 font-normal">(opsional)</span>
          </label>
          <textarea
            id="project-description"
            rows={3}
            placeholder="Ceritakan tujuan project ini..."
            {...register('description')}
            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition resize-none"
          />
        </div>

        <div className="flex gap-3 pt-1">
          <Button type="button" variant="ghost" fullWidth onClick={handleClose}>
            Batal
          </Button>
          <Button type="submit" isLoading={isSubmitting} fullWidth>
            {isSubmitting ? 'Membuat...' : 'Buat Project'}
          </Button>
        </div>
      </form>
    </Modal>
  )
}

export default CreateProjectModal
