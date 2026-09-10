<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    /**
     * Ambil semua project di mana user adalah owner ATAU member.
     * Di-eager load owner & projectMembers untuk menghindari N+1.
     */
    public function index(User $user): LengthAwarePaginator
    {
        return Project::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('projectMembers', fn ($q) => $q->where('user_id', $user->id))
            ->with(['owner', 'projectMembers'])
            ->withCount('tasks')
            ->latest()
            ->paginate(15);
    }

    /**
     * Buat project baru. User pembuat otomatis jadi owner di project_members.
     * Dibungkus transaction agar atomic.
     */
    public function store(User $user, array $data): Project
    {
        return DB::transaction(function () use ($user, $data) {
            $project = Project::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'owner_id'    => $user->id,
            ]);

            // Masukkan owner ke tabel pivot project_members
            $project->projectMembers()->create([
                'user_id' => $user->id,
                'role'    => 'owner',
            ]);

            return $project->load('owner', 'projectMembers');
        });
    }

    /**
     * Ambil detail project beserta relasi yang dibutuhkan.
     */
    public function show(Project $project): Project
    {
        return $project->loadMissing('owner', 'projectMembers', 'tasks');
    }

    /**
     * Update data project.
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->loadMissing('owner', 'projectMembers');
    }

    /**
     * Hapus project beserta semua relasinya (cascade via FK).
     */
    public function destroy(Project $project): void
    {
        $project->delete();
    }

    /**
     * Tambahkan member ke project. Resolve user dari user_id atau email.
     *
     * @throws ValidationException jika user sudah menjadi anggota
     */
    public function addMember(Project $project, array $data): ProjectMember
    {
        // Resolve user
        $user = isset($data['user_id'])
            ? User::findOrFail($data['user_id'])
            : User::where('email', $data['email'])->firstOrFail();

        // Cek apakah sudah menjadi member
        $alreadyMember = $project->projectMembers()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'user_id' => ['User sudah menjadi anggota project ini.'],
            ]);
        }

        return $project->projectMembers()->create([
            'user_id' => $user->id,
            'role'    => $data['role'] ?? 'member',
        ]);
    }
}
