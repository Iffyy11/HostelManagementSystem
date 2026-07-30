<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('warden') ?? false;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
