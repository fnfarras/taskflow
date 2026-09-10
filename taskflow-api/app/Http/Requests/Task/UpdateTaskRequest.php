<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'string', 'in:todo,in_progress,done'],
            'priority'    => ['sometimes', 'string', 'in:low,medium,high'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'     => 'Judul task wajib diisi.',
            'title.max'          => 'Judul task maksimal 255 karakter.',
            'status.in'          => 'Status harus todo, in_progress, atau done.',
            'priority.in'        => 'Priority harus low, medium, atau high.',
            'assignee_id.exists' => 'User yang di-assign tidak ditemukan.',
        ];
    }
}
