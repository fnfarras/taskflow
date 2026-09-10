<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Semua action task memerlukan user adalah owner atau member project terkait.
     * Kita pakai `before()` untuk menyederhanakan — jika bukan member, semua ditolak.
     */
    public function before(User $user, string $ability, mixed ...$args): ?bool
    {
        // Ambil project dari argument pertama (Task atau Project)
        $project = null;

        foreach ($args as $arg) {
            if ($arg instanceof Task) {
                $project = $arg->project;
                break;
            }
            if ($arg instanceof Project) {
                $project = $arg;
                break;
            }
        }

        if ($project && ! $this->isMember($user, $project)) {
            return false;
        }

        return null; // lanjut ke method policy spesifik
    }

    /**
     * Owner/member bisa melihat list task.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return true;
    }

    /**
     * Owner/member bisa melihat detail task.
     */
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    /**
     * Owner/member bisa membuat task.
     */
    public function create(User $user, Project $project): bool
    {
        return true;
    }

    /**
     * Owner/member bisa mengupdate task.
     */
    public function update(User $user, Task $task): bool
    {
        return true;
    }

    /**
     * Owner/member bisa soft delete task.
     */
    public function delete(User $user, Task $task): bool
    {
        return true;
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function isMember(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id
            || $project->projectMembers()->where('user_id', $user->id)->exists();
    }
}
