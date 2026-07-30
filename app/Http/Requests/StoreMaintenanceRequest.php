<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'in:plumbing,electrical,furniture,other'],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
