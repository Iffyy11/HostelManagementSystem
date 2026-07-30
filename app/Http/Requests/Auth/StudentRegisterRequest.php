<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StudentRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $domain = config('hostel.institutional_email_domain');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', "ends_with:@{$domain}"],
            'student_id_number' => ['required', 'string', 'unique:students,student_id_number'],
            'programme' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.ends_with' => 'Registration requires a valid institutional email address.',
        ];
    }
}
