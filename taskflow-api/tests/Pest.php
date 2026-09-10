<?php

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Shared Helper Functions
|--------------------------------------------------------------------------
|
| Helper untuk setup data project yang dipakai di Project dan Task tests.
| Dengan menempatkan di sini, tidak ada duplikasi fungsi antar file test.
|
*/

/**
 * Buat project beserta owner-nya. Otomatis masukkan owner ke project_members.
 * Return: ['owner' => User, 'token' => string, 'project' => Project]
 */
function projectWithOwner(): array
{
    $owner   = User::factory()->create();
    $token   = $owner->createToken('test')->plainTextToken;
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id'    => $owner->id,
        'role'       => 'owner',
    ]);

    return compact('owner', 'token', 'project');
}

/**
 * Buat project dengan owner + satu member biasa.
 * Return: ['owner' => User, 'ownerToken' => string, 'member' => User,
 *          'memberToken' => string, 'project' => Project]
 */
function projectWithMember(): array
{
    $data   = projectWithOwner();
    $member = User::factory()->create();

    ProjectMember::factory()->create([
        'project_id' => $data['project']->id,
        'user_id'    => $member->id,
        'role'       => 'member',
    ]);

    $memberToken = $member->createToken('test')->plainTextToken;

    return array_merge($data, [
        'ownerToken'  => $data['token'],
        'member'      => $member,
        'memberToken' => $memberToken,
    ]);
}

/**
 * Buat user outsider (tidak punya relasi ke project apapun).
 * Return: ['outsider' => User, 'outsiderToken' => string]
 */
function outsider(): array
{
    $outsider      = User::factory()->create();
    $outsiderToken = $outsider->createToken('test')->plainTextToken;

    return compact('outsider', 'outsiderToken');
}
