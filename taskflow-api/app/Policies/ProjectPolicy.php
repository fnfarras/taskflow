<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Semua authenticated user boleh melihat list project.
     * Filtering (hanya project miliknya) dilakukan di Service layer.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Hanya owner atau member project yang bisa melihat detail.
     */
    public function view(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    /**
     * Semua authenticated user bisa membuat project baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Hanya owner yang bisa mengubah project.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->isOwner($user, $project);
    }

    /**
     * Hanya owner yang bisa menghapus project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->isOwner($user, $project);
    }

    /**
     * Hanya owner yang bisa menambahkan member baru.
     */
    public function addMember(User $user, Project $project): bool
    {
        return $this->isOwner($user, $project);
    }

    // -------------------------------------------------------------------------
    // Helper private methods
    // -------------------------------------------------------------------------

    private function isOwner(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id;
    }

    private function isMember(User $user, Project $project): bool
    {
        // Cek apakah user adalah owner langsung atau ada di tabel project_members
        return $this->isOwner($user, $project)
            || $project->projectMembers()
                       ->where('user_id', $user->id)
                       ->exists();
    }
}
