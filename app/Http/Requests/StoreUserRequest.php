<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['student', 'warden', 'caretaker', 'admin'])],
            'student_id_number' => ['required_if:role,student', 'nullable', 'unique:students,student_id_number'],
            'programme' => ['required_if:role,student', 'nullable', 'string'],
            'block_assigned' => ['required_if:role,warden', 'nullable', 'string'],
        ];
    }
}
