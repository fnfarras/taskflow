<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data demo realistis untuk portofolio TaskFlow.
     *
     * Menghasilkan:
     * - 3 user dengan kredensial tetap (mudah diingat saat demo)
     * - 4 project dengan konteks nyata (bukan lorem ipsum)
     * - Setiap project punya 2-3 member
     * - Setiap project punya 8-10 task, tersebar di semua status & priority
     */
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // User Demo
        // ---------------------------------------------------------------------
        $alice = User::factory()->create([
            'name'  => 'Alice Santoso',
            'email' => 'alice@taskflow.dev',
        ]);

        $bob = User::factory()->create([
            'name'  => 'Bob Wijaya',
            'email' => 'bob@taskflow.dev',
        ]);

        $citra = User::factory()->create([
            'name'  => 'Citra Dewi',
            'email' => 'citra@taskflow.dev',
        ]);

        // User keempat sebagai member ekstra di beberapa project
        $deni = User::factory()->create([
            'name'  => 'Deni Pratama',
            'email' => 'deni@taskflow.dev',
        ]);

        $users = [$alice, $bob, $citra, $deni];

        // ---------------------------------------------------------------------
        // Project 1: Website Redesign (Alice sebagai owner)
        // ---------------------------------------------------------------------
        $this->seedProject(
            owner: $alice,
            members: [$bob, $citra],
            name: 'Website Redesign',
            description: 'Redesign tampilan website perusahaan ke desain modern dan responsif.',
            tasks: [
                ['title' => 'Audit halaman yang sudah ada',         'status' => 'done',        'priority' => 'high',   'days' => -5],
                ['title' => 'Buat wireframe desain baru',           'status' => 'done',        'priority' => 'high',   'days' => -3],
                ['title' => 'Review wireframe dengan stakeholder',   'status' => 'done',        'priority' => 'medium', 'days' => -1],
                ['title' => 'Implementasi halaman landing page',     'status' => 'in_progress', 'priority' => 'high',   'days' => 3],
                ['title' => 'Implementasi halaman About Us',         'status' => 'in_progress', 'priority' => 'medium', 'days' => 5],
                ['title' => 'Implementasi halaman Kontak',           'status' => 'todo',        'priority' => 'medium', 'days' => 7],
                ['title' => 'Optimasi gambar dan aset',              'status' => 'todo',        'priority' => 'low',    'days' => 10],
                ['title' => 'Testing cross-browser compatibility',   'status' => 'todo',        'priority' => 'high',   'days' => 12],
                ['title' => 'Deploy ke staging environment',         'status' => 'todo',        'priority' => 'medium', 'days' => 14],
            ],
            assignees: [$alice, $bob, $citra],
        );

        // ---------------------------------------------------------------------
        // Project 2: Mobile App Development (Bob sebagai owner)
        // ---------------------------------------------------------------------
        $this->seedProject(
            owner: $bob,
            members: [$alice, $deni],
            name: 'Mobile App Development',
            description: 'Pengembangan aplikasi mobile untuk tracking kehadiran karyawan.',
            tasks: [
                ['title' => 'Setup project React Native',           'status' => 'done',        'priority' => 'high',   'days' => -10],
                ['title' => 'Desain UI/UX mockup',                  'status' => 'done',        'priority' => 'high',   'days' => -7],
                ['title' => 'Implementasi autentikasi user',         'status' => 'done',        'priority' => 'high',   'days' => -4],
                ['title' => 'Integrasi API absensi',                 'status' => 'in_progress', 'priority' => 'high',   'days' => 2],
                ['title' => 'Fitur notifikasi push',                 'status' => 'in_progress', 'priority' => 'medium', 'days' => 6],
                ['title' => 'Halaman laporan kehadiran',             'status' => 'todo',        'priority' => 'medium', 'days' => 9],
                ['title' => 'Testing di perangkat Android',          'status' => 'todo',        'priority' => 'high',   'days' => 11],
                ['title' => 'Testing di perangkat iOS',              'status' => 'todo',        'priority' => 'high',   'days' => 13],
                ['title' => 'Perbaikan bug dari hasil QA',           'status' => 'todo',        'priority' => 'medium', 'days' => 16],
                ['title' => 'Submit ke Play Store & App Store',      'status' => 'todo',        'priority' => 'low',    'days' => 20],
            ],
            assignees: [$bob, $alice, $deni],
        );

        // ---------------------------------------------------------------------
        // Project 3: Data Migration (Citra sebagai owner)
        // ---------------------------------------------------------------------
        $this->seedProject(
            owner: $citra,
            members: [$bob, $deni],
            name: 'Database Migration Q4',
            description: 'Migrasi database legacy Oracle ke PostgreSQL tanpa downtime.',
            tasks: [
                ['title' => 'Pemetaan skema database lama',         'status' => 'done',        'priority' => 'high',   'days' => -14],
                ['title' => 'Buat skrip migrasi tabel utama',       'status' => 'done',        'priority' => 'high',   'days' => -8],
                ['title' => 'Dry run migrasi di environment test',   'status' => 'done',        'priority' => 'high',   'days' => -3],
                ['title' => 'Identifikasi data yang tidak kompatibel','status' => 'in_progress','priority' => 'high',   'days' => 1],
                ['title' => 'Transformasi data legacy',              'status' => 'in_progress', 'priority' => 'medium', 'days' => 4],
                ['title' => 'Validasi integritas data post-migrasi', 'status' => 'todo',        'priority' => 'high',   'days' => 7],
                ['title' => 'Update connection string di semua service','status' => 'todo',     'priority' => 'medium', 'days' => 8],
                ['title' => 'Rollback plan dokumentasi',             'status' => 'todo',        'priority' => 'medium', 'days' => 5],
            ],
            assignees: [$citra, $bob, $deni],
        );

        // ---------------------------------------------------------------------
        // Project 4: API Integration (Alice sebagai owner, solo project)
        // ---------------------------------------------------------------------
        $this->seedProject(
            owner: $alice,
            members: [$citra],
            name: 'Payment Gateway Integration',
            description: 'Integrasi payment gateway Midtrans ke platform e-commerce.',
            tasks: [
                ['title' => 'Baca dokumentasi Midtrans API',        'status' => 'done',        'priority' => 'medium', 'days' => -6],
                ['title' => 'Setup Midtrans sandbox credentials',    'status' => 'done',        'priority' => 'high',   'days' => -4],
                ['title' => 'Implementasi Snap payment flow',        'status' => 'in_progress', 'priority' => 'high',   'days' => 2],
                ['title' => 'Handle webhook notifikasi pembayaran',  'status' => 'in_progress', 'priority' => 'high',   'days' => 4],
                ['title' => 'Testing skenario pembayaran berhasil',  'status' => 'todo',        'priority' => 'high',   'days' => 6],
                ['title' => 'Testing skenario pembayaran gagal',     'status' => 'todo',        'priority' => 'high',   'days' => 7],
                ['title' => 'Implementasi refund flow',              'status' => 'todo',        'priority' => 'medium', 'days' => 10],
                ['title' => 'Dokumentasi API integration',           'status' => 'todo',        'priority' => 'low',    'days' => 14],
                ['title' => 'Code review & security audit',          'status' => 'todo',        'priority' => 'high',   'days' => 15],
            ],
            assignees: [$alice, $citra],
        );
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Buat satu project lengkap: owner, member, dan task-tasknya.
     *
     * @param  User    $owner     Owner project
     * @param  User[]  $members   User yang akan dijadikan member
     * @param  string  $name      Nama project
     * @param  string  $description
     * @param  array[] $tasks     Array definisi task (title, status, priority, days)
     * @param  User[]  $assignees Pool user yang akan di-assign ke task (round-robin)
     */
    private function seedProject(
        User $owner,
        array $members,
        string $name,
        string $description,
        array $tasks,
        array $assignees,
    ): void {
        DB::transaction(function () use ($owner, $members, $name, $description, $tasks, $assignees) {
            // Buat project
            $project = Project::create([
                'name'        => $name,
                'description' => $description,
                'owner_id'    => $owner->id,
            ]);

            // Owner masuk ke project_members
            ProjectMember::create([
                'project_id' => $project->id,
                'user_id'    => $owner->id,
                'role'       => 'owner',
            ]);

            // Tambahkan member
            foreach ($members as $member) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id'    => $member->id,
                    'role'       => 'member',
                ]);
            }

            // Buat task dengan assignee round-robin dari pool assignees
            foreach ($tasks as $index => $taskData) {
                $assignee = $assignees[$index % count($assignees)];

                Task::create([
                    'project_id'  => $project->id,
                    'assignee_id' => $assignee->id,
                    'title'       => $taskData['title'],
                    'description' => null,
                    'status'      => $taskData['status'],
                    'priority'    => $taskData['priority'],
                    'due_date'    => now()->addDays($taskData['days'])->format('Y-m-d'),
                ]);
            }
        });
    }
}
