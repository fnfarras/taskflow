<?php

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;

/*
|=============================================================================
| TASK FEATURE TESTS
|=============================================================================
| Coverage: GET /projects/{project}/tasks, POST /projects/{project}/tasks,
|           GET /tasks/{task}, PUT /tasks/{task}, DELETE /tasks/{task}
|
| Setiap endpoint: happy path, validation, authorization (403), unauthenticated (401)
*/

// =============================================================================
// GET /api/v1/projects/{project}/tasks
// =============================================================================

describe('GET /api/v1/projects/{project}/tasks', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa melihat semua task dalam project', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    });

    it('[happy] member biasa juga bisa melihat list task', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();
        Task::factory()->count(2)->create(['project_id' => $project->id]);

        $this->withToken($memberToken)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('[happy] filter berdasarkan status=todo', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'done']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'in_progress']);

        $response = $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks?status=todo");

        $response->assertOk()->assertJsonCount(1, 'data');
        expect($response->json('data.0.status'))->toBe('todo');
    });

    it('[happy] filter berdasarkan status=in_progress', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        Task::factory()->count(2)->create(['project_id' => $project->id, 'status' => 'in_progress']);
        Task::factory()->create(['project_id' => $project->id, 'status' => 'done']);

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks?status=in_progress")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('[happy] filter berdasarkan assignee_id', function () {
        ['token' => $token, 'project' => $project, 'owner' => $owner] = projectWithOwner();
        Task::factory()->create(['project_id' => $project->id, 'assignee_id' => $owner->id]);
        Task::factory()->create(['project_id' => $project->id, 'assignee_id' => null]);

        $response = $this->withToken($token)
            ->getJson("/api/v1/projects/{$project->id}/tasks?assignee_id={$owner->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        expect($response->json('data.0.assignee.id'))->toBe($owner->id);
    });

    it('[happy] response memiliki struktur pagination', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message', 'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta'  => ['current_page', 'per_page', 'total'],
            ]);
    });

    it('[happy] list kosong jika belum ada task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] outsider tidak bisa melihat list task → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->getJson("/api/v1/projects/{$project->id}/tasks")->assertStatus(401);
    });
});

// =============================================================================
// POST /api/v1/projects/{project}/tasks
// =============================================================================

describe('POST /api/v1/projects/{project}/tasks', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] berhasil membuat task dengan field minimal (hanya title)', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Implementasi login',
        ])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'data' => ['title' => 'Implementasi login']]);

        $this->assertDatabaseHas('tasks', ['title' => 'Implementasi login', 'project_id' => $project->id]);
    });

    it('[happy] default status adalah todo dan priority adalah medium', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $response = $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Task default',
        ])->assertStatus(201);

        expect($response->json('data.status'))->toBe('todo');
        expect($response->json('data.priority'))->toBe('medium');
    });

    it('[happy] bisa membuat task dengan semua field sekaligus', function () {
        ['token' => $token, 'project' => $project, 'owner' => $owner] = projectWithOwner();

        $response = $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'       => 'Full task',
            'description' => 'Deskripsi lengkap.',
            'priority'    => 'high',
            'assignee_id' => $owner->id,
            'due_date'    => now()->addDays(7)->toDateString(),
        ])->assertStatus(201);

        expect($response->json('data.priority'))->toBe('high');
        expect($response->json('data.assignee.id'))->toBe($owner->id);
    });

    it('[happy] member biasa juga bisa membuat task', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();

        $this->withToken($memberToken)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Task dari member',
        ])->assertStatus(201);
    });

    it('[happy] bisa assign task ke member project', function () {
        ['token' => $token, 'project' => $project, 'member' => $member] = projectWithMember();

        $response = $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'       => 'Task untuk member',
            'assignee_id' => $member->id,
        ])->assertStatus(201);

        expect($response->json('data.assignee.id'))->toBe($member->id);
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] outsider tidak bisa membuat task → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Hacked',
        ])->assertStatus(403);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika title tidak diisi → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.title.0', 'Judul task wajib diisi.');
    });

    it('[validation] gagal jika assignee bukan member project → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        ['outsider' => $nonMember] = outsider();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'       => 'Task gagal',
            'assignee_id' => $nonMember->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.assignee_id.0', 'User yang di-assign harus menjadi anggota project ini terlebih dahulu.');
    });

    it('[validation] gagal jika priority tidak valid → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'    => 'Task priority salah',
            'priority' => 'critical',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.priority.0', 'Priority harus low, medium, atau high.');
    });

    it('[validation] gagal jika due_date sudah lewat → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'    => 'Overdue task',
            'due_date' => now()->subDay()->toDateString(),
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.due_date.0', 'Due date tidak boleh sebelum hari ini.');
    });

    it('[validation] gagal jika assignee_id user tidak ada di database → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title'       => 'Task',
            'assignee_id' => 99999,
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.assignee_id.0', 'User yang di-assign tidak ditemukan.');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->postJson("/api/v1/projects/{$project->id}/tasks", ['title' => 'Test'])
            ->assertStatus(401);
    });
});

// =============================================================================
// GET /api/v1/tasks/{task}  (shallow route)
// =============================================================================

describe('GET /api/v1/tasks/{task}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa melihat detail task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['success' => true, 'data' => ['id' => $task->id, 'title' => $task->title]]);
    });

    it('[happy] member biasa bisa melihat detail task', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($memberToken)->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
    });

    it('[happy] response memiliki field assignee dan project_id', function () {
        ['token' => $token, 'project' => $project, 'owner' => $owner] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => $owner->id]);

        $response = $this->withToken($token)->getJson("/api/v1/tasks/{$task->id}")->assertOk();

        expect($response->json('data.project_id'))->toBe($project->id);
        expect($response->json('data.assignee.id'))->toBe($owner->id);
    });

    it('[happy] assignee null jika task belum di-assign', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => null]);

        $response = $this->withToken($token)->getJson("/api/v1/tasks/{$task->id}")->assertOk();

        expect($response->json('data.assignee'))->toBeNull();
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] outsider tidak bisa melihat detail task → 403', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->getJson("/api/v1/tasks/{$task->id}")
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->getJson("/api/v1/tasks/{$task->id}")->assertStatus(401);
    });
});

// =============================================================================
// PUT /api/v1/tasks/{task}  (shallow route)
// =============================================================================

describe('PUT /api/v1/tasks/{task}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa mengubah status task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id, 'status' => 'todo']);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    });

    it('[happy] bisa mengubah priority task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id, 'priority' => 'low']);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['priority' => 'high'])
            ->assertOk()
            ->assertJsonPath('data.priority', 'high');
    });

    it('[happy] bisa assign ke member project', function () {
        ['token' => $token, 'project' => $project, 'member' => $member] = projectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $response = $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", [
            'assignee_id' => $member->id,
        ])->assertOk();

        expect($response->json('data.assignee.id'))->toBe($member->id);
    });

    it('[happy] bisa mengosongkan assignee dengan set null', function () {
        ['token' => $token, 'project' => $project, 'owner' => $owner] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id, 'assignee_id' => $owner->id]);

        $response = $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", [
            'assignee_id' => null,
        ])->assertOk();

        expect($response->json('data.assignee'))->toBeNull();
    });

    it('[happy] bisa mengubah title dan description', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", [
            'title'       => 'Judul baru',
            'description' => 'Deskripsi baru.',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul baru');
    });

    it('[happy] bisa mengubah due_date', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task    = Task::factory()->create(['project_id' => $project->id]);
        $newDate = now()->addDays(14)->toDateString();

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['due_date' => $newDate])
            ->assertOk()
            ->assertJsonPath('data.due_date', $newDate);
    });

    it('[happy] member biasa juga bisa mengupdate task', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($memberToken)->putJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] outsider tidak bisa update task → 403', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])
            ->assertStatus(403);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika status tidak valid → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['status' => 'invalid'])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.status.0', 'Status harus todo, in_progress, atau done.');
    });

    it('[validation] gagal jika priority tidak valid → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['priority' => 'urgent'])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.priority.0', 'Priority harus low, medium, atau high.');
    });

    it('[validation] gagal jika title dikirim tapi kosong → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", ['title' => ''])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.title.0', 'Judul task wajib diisi.');
    });

    it('[validation] gagal jika assignee bukan member project → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);
        ['outsider' => $nonMember] = outsider();

        $this->withToken($token)->putJson("/api/v1/tasks/{$task->id}", [
            'assignee_id' => $nonMember->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.assignee_id.0', 'User yang di-assign harus menjadi anggota project ini terlebih dahulu.');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->putJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])->assertStatus(401);
    });
});

// =============================================================================
// DELETE /api/v1/tasks/{task}  (shallow route, soft delete)
// =============================================================================

describe('DELETE /api/v1/tasks/{task}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner berhasil soft delete task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['success' => true, 'data' => null]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    });

    it('[happy] soft deleted task tidak muncul di list task', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($token)->deleteJson("/api/v1/tasks/{$task->id}")->assertOk();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}/tasks")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('[happy] member biasa juga bisa soft delete task', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->withToken($memberToken)->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertOk();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] outsider tidak bisa delete task → 403', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertStatus(403);

        // Task masih ada, tidak terhapus
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertStatus(401);
    });
});
