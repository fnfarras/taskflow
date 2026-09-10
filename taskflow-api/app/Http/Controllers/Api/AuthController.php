<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * POST /api/v1/register
     * Daftarkan user baru dan kembalikan token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data'    => [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ], 201);
    }

    /**
     * POST /api/v1/login
     * Login user dan kembalikan token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ]);
    }

    /**
     * POST /api/v1/logout
     * Revoke token yang sedang aktif. Membutuhkan auth:sanctum.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data'    => null,
        ]);
    }

    /**
     * GET /api/v1/me
     * Ambil data user yang sedang login. Membutuhkan auth:sanctum.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data'    => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }
}
