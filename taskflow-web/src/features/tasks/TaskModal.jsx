import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import Modal from '@/components/ui/Modal'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import ApiErrorAlert from '@/components/ui/ApiErrorAlert'

// ---------------------------------------------------------------------------
// Schema validasi — mode create dan edit berbeda
// ---------------------------------------------------------------------------
const createSchema = z.object({
  title:       z.string().min(1, 'Judul task wajib diisi.').max(255),
  description: z.string().optional(),
  assignee_id: z.coerce.number().optional().nullable(),
  priority:    z.enum(['low', 'medium', 'high']).default('medium'),
  due_date:    z.string().optional().nullable(),
})

const editSchema = createSchema.extend({
  status: z.enum(['todo', 'in_progress', 'done']),
})

// ---------------------------------------------------------------------------
// Field Select reusable (hanya untuk komponen ini)
// ---------------------------------------------------------------------------
const SelectField = ({ label, id, children, error, containerClassName = '', className = '', ...rest }) => (
  <div className={`flex flex-col gap-1.5 ${containerClassName}`}>
    {label && <label htmlFor={id} className="text-sm font-medium text-slate-300">{label}</label>}
    <select
      id={id}
      className={`w-full bg-slate-900 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/50 transition [&>option]:bg-slate-900 [&>option]:text-slate-100 ${className}`}
      {...rest}
    >
      {children}
    </select>
    {error && <p className="text-xs text-red-400">{error}</p>}
  </div>
)

// ---------------------------------------------------------------------------
// TaskModal — create dan edit dalam satu komponen
// mode: 'create' | 'edit'
// ---------------------------------------------------------------------------
const TaskModal = ({ isOpen, onClose, mode = 'create', task = null, members = [], onCreate, onUpdate, onDelete }) => {
  const isEdit = mode === 'edit'
  const [apiError, setApiError] = useState(null)
  const [confirmDelete, setConfirmDelete] = useState(false)

  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(isEdit ? editSchema : createSchema),
    defaultValues: isEdit && task ? {
      title:       task.title,
      description: task.description ?? '',
      assignee_id: task.assignee?.id ?? '',
      priority:    task.priority,
      status:      task.status,
      due_date:    task.due_date ?? '',
    } : {
      title: '', description: '', assignee_id: '', priority: 'medium', due_date: '',
    },
  })

  // Reset form saat task berubah (buka edit task berbeda)
  useEffect(() => {
    if (isEdit && task) {
      reset({
        title:       task.title,
        description: task.description ?? '',
        assignee_id: task.assignee?.id ?? '',
        priority:    task.priority,
        status:      task.status,
        due_date:    task.due_date ?? '',
      })
    } else {
      reset({ title: '', description: '', assignee_id: '', priority: 'medium', due_date: '' })
    }
    setApiError(null)
    setConfirmDelete(false)
  }, [task, isEdit, reset, isOpen])

  const onSubmit = async (data) => {
    setApiError(null)
    // Bersihkan field kosong
    const payload = {
      ...data,
      assignee_id: data.assignee_id || null,
      due_date:    data.due_date || null,
      description: data.description || null,
    }
    try {
      if (isEdit) {
        await onUpdate({ id: task.id, ...payload })
      } else {
        await onCreate(payload)
      }
      onClose()
    } catch (err) {
      const errs = err?.response?.data?.data?.errors
      setApiError(errs ? Object.values(errs).flat()[0] : err?.response?.data?.message ?? 'Terjadi kesalahan.')
    }
  }

  const handleDelete = async () => {
    if (!confirmDelete) { setConfirmDelete(true); return }
    try {
      await onDelete(task.id)
      onClose()
    } catch {
      setApiError('Gagal menghapus task.')
    }
  }

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={isEdit ? 'Edit Task' : 'Tambah Task Baru'}
      size="lg"
    >
      <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-4">
        <ApiErrorAlert message={apiError} onClose={() => setApiError(null)} />

        {/* Title */}
        <Input
          label="Judul Task"
          id="task-title"
          placeholder="Apa yang perlu dikerjakan?"
          error={errors.title?.message}
          {...register('title')}
        />

        {/* Description */}
        <div className="flex flex-col gap-1.5">
          <label className="text-sm font-medium text-slate-300">
            Deskripsi <span className="text-slate-500 font-normal">(opsional)</span>
          </label>
          <textarea
            rows={2}
            placeholder="Detail tambahan..."
            {...register('description')}
            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition resize-none"
          />
        </div>

        {/* Row: status (edit only) + priority */}
        <div className="grid grid-cols-2 gap-3">
          {isEdit && (
            <SelectField label="Status" id="task-status" {...register('status')}>
              <option value="todo" className="bg-slate-900 text-slate-100">To Do</option>
              <option value="in_progress" className="bg-slate-900 text-slate-100">In Progress</option>
              <option value="done" className="bg-slate-900 text-slate-100">Done</option>
            </SelectField>
          )}
          <SelectField
            label="Priority"
            id="task-priority"
            containerClassName={!isEdit ? 'col-span-2' : ''}
            {...register('priority')}
          >
            <option value="low" className="bg-slate-900 text-slate-100">↓ Low</option>
            <option value="medium" className="bg-slate-900 text-slate-100">→ Medium</option>
            <option value="high" className="bg-slate-900 text-slate-100">↑ High</option>
          </SelectField>
        </div>

        {/* Row: assignee + due_date */}
        <div className="grid grid-cols-2 gap-3">
          <SelectField label="Assignee" id="task-assignee" {...register('assignee_id')}>
            <option value="" className="bg-slate-900 text-slate-100">Unassigned</option>
            {members.map((m) => (
              <option key={m.user_id} value={m.user_id} className="bg-slate-900 text-slate-100">
                {m.user?.name ?? `User #${m.user_id}`}
              </option>
            ))}
          </SelectField>
          <Input
            label="Due Date"
            id="task-due-date"
            type="date"
            error={errors.due_date?.message}
            {...register('due_date')}
          />
        </div>

        {/* Buttons */}
        <div className={`flex gap-3 pt-1 ${isEdit ? 'justify-between' : ''}`}>
          {isEdit && (
            <Button
              type="button"
              variant="danger"
              onClick={handleDelete}
              className="shrink-0"
            >
              {confirmDelete ? 'Konfirmasi Hapus?' : 'Hapus'}
            </Button>
          )}
          <div className="flex gap-3 flex-1 justify-end">
            <Button type="button" variant="ghost" onClick={onClose}>Batal</Button>
            <Button type="submit" isLoading={isSubmitting}>
              {isSubmitting ? 'Menyimpan...' : isEdit ? 'Simpan' : 'Buat Task'}
            </Button>
          </div>
        </div>
      </form>
    </Modal>
  )
}

export default TaskModal
