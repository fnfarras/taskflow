<?php

namespace App\Services;

use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Daftarkan user baru dan kembalikan user beserta token-nya.
     *
     * @return array{user: User, token: string}
     */
    public function register(RegisterRequest $request): array
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // di-hash otomatis oleh cast 'hashed' di Model
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * Login user dan kembalikan user beserta token baru.
     *
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(LoginRequest $request): array
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Hapus token lama untuk menghindari akumulasi token (single-session per device)
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * Logout user dengan mencabut (revoke) token yang sedang aktif.
     */
    public function logout(User $user): void
    {
        // Hanya revoke token yang digunakan untuk request ini, bukan semua token
        $user->currentAccessToken()->delete();
    }
}
