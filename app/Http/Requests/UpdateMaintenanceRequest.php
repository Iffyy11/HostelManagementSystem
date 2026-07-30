<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['caretaker', 'warden', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:open,in_progress,resolved'],
            'assigned_caretaker_id' => ['nullable', 'exists:users,id'],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
