<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required_without:email', 'nullable', 'integer', 'exists:users,id'],
            'email'   => ['required_without:user_id', 'nullable', 'email', 'exists:users,email'],
            'role'    => ['sometimes', 'string', 'in:owner,member'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required_without' => 'User ID atau email wajib diisi.',
            'user_id.exists'           => 'User tidak ditemukan.',
            'email.required_without'   => 'Email atau user_id wajib diisi.',
            'email.exists'             => 'User dengan email tersebut tidak ditemukan.',
            'role.in'                  => 'Role harus owner atau member.',
        ];
    }
}
