<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority'    => ['sometimes', 'string', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul task wajib diisi.',
            'title.max'            => 'Judul task maksimal 255 karakter.',
            'assignee_id.exists'   => 'User yang di-assign tidak ditemukan.',
            'priority.in'          => 'Priority harus low, medium, atau high.',
            'due_date.after_or_equal' => 'Due date tidak boleh sebelum hari ini.',
        ];
    }
}
