<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * List task dalam project dengan filter opsional: status dan assignee_id.
     */
    public function index(Project $project, array $filters = []): LengthAwarePaginator
    {
        return $project->tasks()
            ->with('assignee')
            ->when(
                ! empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['assignee_id']),
                fn ($q) => $q->where('assignee_id', $filters['assignee_id'])
            )
            ->latest()
            ->paginate(15);
    }

    /**
     * Buat task baru dalam project.
     * Validasi bahwa assignee (jika ada) adalah member project.
     *
     * @throws ValidationException
     */
    public function store(Project $project, array $data): Task
    {
        if (! empty($data['assignee_id'])) {
            $this->assertAssigneeIsMember($project, (int) $data['assignee_id']);
        }

        $task = $project->tasks()->create([
            'assignee_id' => $data['assignee_id'] ?? null,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'todo',
            'priority'    => $data['priority'] ?? 'medium',
            'due_date'    => $data['due_date'] ?? null,
        ]);

        return $task->load('assignee');
    }

    /**
     * Ambil detail task.
     */
    public function show(Task $task): Task
    {
        return $task->loadMissing('assignee', 'project');
    }

    /**
     * Update task (partial update — hanya field yang dikirim).
     *
     * @throws ValidationException
     */
    public function update(Task $task, array $data): Task
    {
        // Jika assignee diubah, pastikan user baru adalah member project
        if (array_key_exists('assignee_id', $data) && ! is_null($data['assignee_id'])) {
            $this->assertAssigneeIsMember($task->project, (int) $data['assignee_id']);
        }

        $task->update($data);

        return $task->loadMissing('assignee');
    }

    /**
     * Soft delete task.
     */
    public function destroy(Task $task): void
    {
        $task->delete();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Pastikan assignee adalah member atau owner project.
     *
     * @throws ValidationException
     */
    private function assertAssigneeIsMember(Project $project, int $assigneeId): void
    {
        $isMember = $project->owner_id === $assigneeId
            || $project->projectMembers()->where('user_id', $assigneeId)->exists();

        if (! $isMember) {
            throw ValidationException::withMessages([
                'assignee_id' => ['User yang di-assign harus menjadi anggota project ini terlebih dahulu.'],
            ]);
        }
    }
}
