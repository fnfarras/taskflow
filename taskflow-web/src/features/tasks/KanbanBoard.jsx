import { useState } from 'react'
import TaskCard from './TaskCard'
import TaskModal from './TaskModal'
import Skeleton from '@/components/ui/Skeleton'
import Button from '@/components/ui/Button'

const COLUMNS = [
  { key: 'todo',        label: 'To Do',       dotClass: 'bg-slate-400' },
  { key: 'in_progress', label: 'In Progress',  dotClass: 'bg-amber-400' },
  { key: 'done',        label: 'Done',         dotClass: 'bg-emerald-400' },
]

/**
 * KanbanBoard — 3 kolom: Todo, In Progress, Done.
 * Task dikelompokkan berdasarkan status.
 */
const KanbanBoard = ({ tasks, isLoading, members, onCreateTask, onUpdateTask, onDeleteTask }) => {
  const [modal, setModal] = useState({ open: false, mode: 'create', task: null })

  const openCreate = () => setModal({ open: true, mode: 'create', task: null })
  const openEdit   = (task) => setModal({ open: true, mode: 'edit', task })
  const closeModal = () => setModal((m) => ({ ...m, open: false }))

  const byStatus = COLUMNS.reduce((acc, col) => {
    acc[col.key] = tasks.filter((t) => t.status === col.key)
    return acc
  }, {})

  return (
    <>
      {/* Toolbar */}
      <div className="flex items-center justify-between mb-4">
        <h2 className="text-sm font-semibold text-slate-400 uppercase tracking-wider">
          Task Board
        </h2>
        <Button size="sm" onClick={openCreate}>
          + Tambah Task
        </Button>
      </div>

      {/* Board */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {COLUMNS.map((col) => (
          <div key={col.key} className="flex flex-col gap-3">
            {/* Column Header */}
            <div className="flex items-center gap-2 px-1">
              <span className={`w-2 h-2 rounded-full ${col.dotClass}`} />
              <span className="text-sm font-semibold text-slate-300">{col.label}</span>
              <span className="ml-auto text-xs text-slate-600 font-medium bg-white/5 rounded-full px-2 py-0.5">
                {isLoading ? '—' : byStatus[col.key]?.length ?? 0}
              </span>
            </div>

            {/* Cards */}
            <div className="flex flex-col gap-2 min-h-[120px]">
              {isLoading ? (
                <>
                  <Skeleton.TaskCard />
                  <Skeleton.TaskCard />
                </>
              ) : byStatus[col.key]?.length > 0 ? (
                byStatus[col.key].map((task) => (
                  <TaskCard key={task.id} task={task} onClick={() => openEdit(task)} />
                ))
              ) : (
                <div className="flex items-center justify-center h-20 rounded-xl border border-dashed border-white/10 text-slate-600 text-xs">
                  Belum ada task
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Task Modal */}
      <TaskModal
        isOpen={modal.open}
        onClose={closeModal}
        mode={modal.mode}
        task={modal.task}
        members={members}
        onCreate={onCreateTask}
        onUpdate={onUpdateTask}
        onDelete={onDeleteTask}
      />
    </>
  )
}

export default KanbanBoard
