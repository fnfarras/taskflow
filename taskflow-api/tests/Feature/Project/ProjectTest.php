<?php

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

/*
|=============================================================================
| PROJECT FEATURE TESTS
|=============================================================================
| Coverage: GET /projects, POST /projects, GET /projects/{id},
|           PUT /projects/{id}, DELETE /projects/{id},
|           POST /projects/{id}/members
|
| Setiap endpoint: happy path, validation, authorization (403), unauthenticated (401)
*/

// =============================================================================
// GET /api/v1/projects
// =============================================================================

describe('GET /api/v1/projects', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] mengembalikan project di mana user adalah owner', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.id', $project->id);
    });

    it('[happy] mengembalikan project di mana user adalah member biasa', function () {
        ['project' => $project]                      = projectWithOwner();
        ['outsider' => $member, 'outsiderToken' => $memberToken] = outsider();

        ProjectMember::factory()->create([
            'project_id' => $project->id,
            'user_id'    => $member->id,
            'role'       => 'member',
        ]);

        $ids = collect(
            $this->withToken($memberToken)->getJson('/api/v1/projects')
                ->assertOk()
                ->json('data')
        )->pluck('id');

        expect($ids)->toContain($project->id);
    });

    it('[happy] tidak menampilkan project yang bukan milik/anggota user', function () {
        ['token' => $token]     = projectWithOwner();
        $lainProject = Project::factory()->create(); // project orang lain

        $ids = collect(
            $this->withToken($token)->getJson('/api/v1/projects')->json('data')
        )->pluck('id');

        expect($ids)->not->toContain($lainProject->id);
    });

    it('[happy] response memiliki struktur pagination yang benar', function () {
        ['token' => $token] = projectWithOwner();

        $this->withToken($token)->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta'  => ['current_page', 'per_page', 'total'],
            ]);
    });

    it('[happy] list kosong jika user tidak punya project', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        $this->getJson('/api/v1/projects')->assertStatus(401);
    });
});

// =============================================================================
// POST /api/v1/projects
// =============================================================================

describe('POST /api/v1/projects', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] berhasil membuat project dan mengembalikan 201', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', [
            'name'        => 'Project Baru',
            'description' => 'Deskripsi project.',
        ])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'data' => ['name' => 'Project Baru']]);

        $this->assertDatabaseHas('projects', ['name' => 'Project Baru', 'owner_id' => $user->id]);
    });

    it('[happy] user pembuat otomatis masuk ke project_members dengan role owner', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', ['name' => 'Alpha'])
            ->assertStatus(201);

        $project = Project::where('owner_id', $user->id)->first();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role'       => 'owner',
        ]);
    });

    it('[happy] response mengandung data owner', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/projects', ['name' => 'Beta'])
            ->assertStatus(201);

        expect($response->json('data.owner.id'))->toBe($user->id);
    });

    it('[happy] description bersifat opsional', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', ['name' => 'No Desc'])
            ->assertStatus(201)
            ->assertJsonPath('data.description', null);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika name tidak diisi → 422', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', [])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('data.errors.name.0', 'Nama project wajib diisi.');
    });

    it('[validation] gagal jika name melebihi 255 karakter → 422', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/projects', ['name' => str_repeat('a', 256)])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.name.0', 'Nama project maksimal 255 karakter.');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        $this->postJson('/api/v1/projects', ['name' => 'Test'])->assertStatus(401);
    });
});

// =============================================================================
// GET /api/v1/projects/{project}
// =============================================================================

describe('GET /api/v1/projects/{project}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa melihat detail project', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJson(['success' => true, 'data' => ['id' => $project->id, 'name' => $project->name]]);
    });

    it('[happy] member biasa juga bisa melihat detail project', function () {
        ['project' => $project] = projectWithOwner();
        ['outsider' => $member, 'outsiderToken' => $memberToken] = outsider();

        ProjectMember::factory()->create([
            'project_id' => $project->id,
            'user_id'    => $member->id,
            'role'       => 'member',
        ]);

        $this->withToken($memberToken)->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $project->id);
    });

    it('[happy] response memiliki struktur lengkap dengan owner', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description', 'owner', 'created_at'],
            ]);
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] user bukan member tidak bisa melihat detail → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->getJson("/api/v1/projects/{$project->id}")
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->getJson("/api/v1/projects/{$project->id}")->assertStatus(401);
    });

    // --- Not Found ------------------------------------------------------------

    it('[not found] project tidak ada → 404', function () {
        ['token' => $token] = projectWithOwner();

        $this->withToken($token)->getJson('/api/v1/projects/99999')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    });
});

// =============================================================================
// PUT /api/v1/projects/{project}
// =============================================================================

describe('PUT /api/v1/projects/{project}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa mengupdate nama project', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", ['name' => 'Nama Baru'])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Nama Baru');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Nama Baru']);
    });

    it('[happy] owner bisa mengupdate deskripsi saja (partial update)', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", [
            'description' => 'Deskripsi baru.',
        ])
            ->assertOk()
            ->assertJsonPath('data.description', 'Deskripsi baru.');
    });

    it('[happy] bisa menghapus deskripsi dengan set null', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", [
            'description' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.description', null);
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] member biasa tidak bisa update project → 403', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();

        $this->withToken($memberToken)->putJson("/api/v1/projects/{$project->id}", ['name' => 'Coba Ubah'])
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    });

    it('[authorization] outsider tidak bisa update project → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", ['name' => 'Hacked'])
            ->assertStatus(403);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika name dikirim tapi kosong → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", ['name' => ''])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.name.0', 'Nama project wajib diisi.');
    });

    it('[validation] gagal jika name melebihi 255 karakter → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->putJson("/api/v1/projects/{$project->id}", [
            'name' => str_repeat('a', 256),
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.name.0', 'Nama project maksimal 255 karakter.');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->putJson("/api/v1/projects/{$project->id}", ['name' => 'Test'])->assertStatus(401);
    });
});

// =============================================================================
// DELETE /api/v1/projects/{project}
// =============================================================================

describe('DELETE /api/v1/projects/{project}', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner berhasil menghapus project', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->deleteJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJson(['success' => true, 'data' => null]);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    });

    it('[happy] menghapus project juga menghapus project_members terkait (cascade)', function () {
        ['token' => $token, 'project' => $project, 'owner' => $owner] = projectWithOwner();

        $this->withToken($token)->deleteJson("/api/v1/projects/{$project->id}")->assertOk();

        $this->assertDatabaseMissing('project_members', ['project_id' => $project->id]);
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] member biasa tidak bisa menghapus project → 403', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();

        $this->withToken($memberToken)->deleteJson("/api/v1/projects/{$project->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    });

    it('[authorization] outsider tidak bisa menghapus project → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->deleteJson("/api/v1/projects/{$project->id}")
            ->assertStatus(403);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->deleteJson("/api/v1/projects/{$project->id}")->assertStatus(401);
    });
});

// =============================================================================
// POST /api/v1/projects/{project}/members
// =============================================================================

describe('POST /api/v1/projects/{project}/members', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] owner bisa menambahkan member via email', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $newUser = User::factory()->create();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => $newUser->email,
        ])
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => ['user_id' => $newUser->id, 'role' => 'member'],
            ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $newUser->id,
            'role'       => 'member',
        ]);
    });

    it('[happy] owner bisa menambahkan member via user_id', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $newUser = User::factory()->create();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
            'role'    => 'member',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $newUser->id);
    });

    it('[happy] bisa menambahkan member dengan role owner', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $newUser = User::factory()->create();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
            'role'    => 'owner',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.role', 'owner');
    });

    // --- Authorization Failure ------------------------------------------------

    it('[authorization] member biasa tidak bisa menambahkan member → 403', function () {
        ['project' => $project, 'memberToken' => $memberToken] = projectWithMember();
        $newUser = User::factory()->create();

        $this->withToken($memberToken)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
        ])->assertStatus(403);
    });

    it('[authorization] outsider tidak bisa menambahkan member → 403', function () {
        ['project' => $project] = projectWithOwner();
        ['outsiderToken' => $token] = outsider();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => User::factory()->create()->id,
        ])->assertStatus(403);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika user sudah menjadi anggota → 422', function () {
        ['token' => $token, 'project' => $project, 'member' => $member] = projectWithMember();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.user_id.0', 'User sudah menjadi anggota project ini.');
    });

    it('[validation] gagal jika email tidak terdaftar di sistem → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => 'tidakada@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'User dengan email tersebut tidak ditemukan.');
    });

    it('[validation] gagal jika user_id tidak ada di database → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => 99999,
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.user_id.0', 'User tidak ditemukan.');
    });

    it('[validation] gagal jika tidak ada user_id maupun email → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.user_id.0', 'User ID atau email wajib diisi.');
    });

    it('[validation] gagal jika role tidak valid → 422', function () {
        ['token' => $token, 'project' => $project] = projectWithOwner();
        $newUser = User::factory()->create();

        $this->withToken($token)->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $newUser->id,
            'role'    => 'superadmin',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.role.0', 'Role harus owner atau member.');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] tanpa token → 401', function () {
        ['project' => $project] = projectWithOwner();

        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => 1,
        ])->assertStatus(401);
    });
});
