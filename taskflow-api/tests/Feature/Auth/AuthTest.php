<?php

use App\Models\User;

/*
|=============================================================================
| AUTH FEATURE TESTS
|=============================================================================
| Coverage: POST /register, POST /login, POST /logout, GET /me
| Setiap endpoint: happy path, validation failure, unauthenticated (jika berlaku)
*/

// =============================================================================
// POST /api/v1/register
// =============================================================================

describe('POST /api/v1/register', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] berhasil mendaftarkan user baru dan mengembalikan 201 + token', function () {
        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'success', 'message',
                'data' => [
                    'user'  => ['id', 'name', 'email', 'created_at'],
                    'token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data'    => ['user' => ['email' => 'budi@example.com']],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    });

    it('[happy] response tidak mengekspos password di data user', function () {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'Andi',
            'email'                 => 'andi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        expect($response->json('data.user'))->not->toHaveKey('password');
        expect($response->json('data.user'))->not->toHaveKey('remember_token');
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika name tidak diisi → 422', function () {
        $this->postJson('/api/v1/register', [
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('data.errors.name.0', 'Nama wajib diisi.');
    });

    it('[validation] gagal jika email tidak diisi → 422', function () {
        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'Email wajib diisi.');
    });

    it('[validation] gagal jika email format tidak valid → 422', function () {
        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi',
            'email'                 => 'bukan-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'Format email tidak valid.');
    });

    it('[validation] gagal jika email sudah terdaftar → 422', function () {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi Lain',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'Email sudah terdaftar.');
    });

    it('[validation] gagal jika password tidak diisi → 422', function () {
        $this->postJson('/api/v1/register', [
            'name'  => 'Budi',
            'email' => 'budi@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.password.0', 'Password wajib diisi.');
    });

    it('[validation] gagal jika password kurang dari 8 karakter → 422', function () {
        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi',
            'email'                 => 'budi@example.com',
            'password'              => 'abc123',
            'password_confirmation' => 'abc123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.password.0', 'Password minimal 8 karakter.');
    });

    it('[validation] gagal jika password_confirmation tidak cocok → 422', function () {
        $this->postJson('/api/v1/register', [
            'name'                  => 'Budi',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'berbeda123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.password.0', 'Konfirmasi password tidak cocok.');
    });
});

// =============================================================================
// POST /api/v1/login
// =============================================================================

describe('POST /api/v1/login', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] berhasil login dan mengembalikan 200 + token', function () {
        User::factory()->create([
            'email'    => 'budi@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/v1/login', [
            'email'    => 'budi@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email'], 'token'],
            ]);
    });

    it('[happy] login menghapus token lama dan menerbitkan token baru', function () {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $user->createToken('old-token'); // token lama

        $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertOk();

        // Hanya 1 token aktif (token lama dihapus)
        $this->assertDatabaseCount('personal_access_tokens', 1);
    });

    // --- Validation Failure ---------------------------------------------------

    it('[validation] gagal jika email tidak diisi → 422', function () {
        $this->postJson('/api/v1/login', ['password' => 'password123'])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'Email wajib diisi.');
    });

    it('[validation] gagal jika password tidak diisi → 422', function () {
        $this->postJson('/api/v1/login', ['email' => 'budi@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.password.0', 'Password wajib diisi.');
    });

    it('[validation] gagal jika email format tidak valid → 422', function () {
        $this->postJson('/api/v1/login', [
            'email'    => 'bukan-email',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'Format email tidak valid.');
    });

    it('[validation] gagal jika password salah → 422 dengan pesan kredensial', function () {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/v1/login', [
            'email'    => 'budi@example.com',
            'password' => 'passwordsalah',
        ])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('data.errors.email.0', 'Kredensial yang diberikan tidak cocok dengan data kami.');
    });

    it('[validation] gagal jika email belum terdaftar → 422', function () {
        $this->postJson('/api/v1/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    });
});

// =============================================================================
// POST /api/v1/logout
// =============================================================================

describe('POST /api/v1/logout', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] berhasil logout dan token dihapus dari database', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Logout berhasil.', 'data' => null]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });

    it('[happy] logout hanya merevoke token aktif, tidak semua token user', function () {
        $user        = User::factory()->create();
        $activeToken = $user->createToken('aktif')->plainTextToken;
        $user->createToken('lain'); // token lain yang tidak dipakai request ini

        $this->withToken($activeToken)->postJson('/api/v1/logout')->assertOk();

        // Masih ada 1 token (yang tidak dipakai untuk logout)
        $this->assertDatabaseCount('personal_access_tokens', 1);
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] request tanpa token → 401', function () {
        $this->postJson('/api/v1/logout')->assertStatus(401);
    });

    it('[unauthenticated] request dengan token tidak valid → 401', function () {
        $this->withToken('token-palsu')->postJson('/api/v1/logout')->assertStatus(401);
    });
});

// =============================================================================
// GET /api/v1/me
// =============================================================================

describe('GET /api/v1/me', function () {

    // --- Happy Path -----------------------------------------------------------

    it('[happy] mengembalikan data user yang sedang login', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data'    => ['user' => ['id' => $user->id, 'email' => $user->email]],
            ]);
    });

    it('[happy] response tidak mengekspos password di data user', function () {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me')->assertOk();

        expect($response->json('data.user'))->not->toHaveKey('password');
        expect($response->json('data.user'))->not->toHaveKey('remember_token');
    });

    // --- Unauthenticated ------------------------------------------------------

    it('[unauthenticated] request tanpa token → 401', function () {
        $this->getJson('/api/v1/me')->assertStatus(401);
    });
});
