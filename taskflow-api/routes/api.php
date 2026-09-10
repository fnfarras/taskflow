<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — TaskFlow
|--------------------------------------------------------------------------
| Prefix /api (dari bootstrap/app.php) + v1 di sini.
| Semua route CRUD dan Auth dilindungi auth:sanctum kecuali register & login.
*/

Route::prefix('v1')->group(function () {

    // -----------------------------------------------------------------------
    // Auth — Public
    // -----------------------------------------------------------------------
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // -----------------------------------------------------------------------
    // Protected Routes
    // -----------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Projects CRUD
        Route::apiResource('projects', ProjectController::class);

        // Add member ke project (di luar apiResource karena bukan CRUD standard)
        Route::post('projects/{project}/members', [ProjectController::class, 'addMember'])
             ->name('projects.members.store');

        // Tasks CRUD — nested di bawah project, dengan shallow() agar
        // show/update/delete tidak membutuhkan project_id di URL
        Route::apiResource('projects.tasks', TaskController::class)->shallow();
    });
});
