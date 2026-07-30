<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoveOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('student');
    }

    public function rules(): array
    {
        $minDate = now()->addDays(config('hostel.move_out_notice_days'))->toDateString();

        return [
            'intended_move_out_date' => ['required', 'date', 'after_or_equal:'.$minDate],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        $days = config('hostel.move_out_notice_days');

        return [
            'intended_move_out_date.after_or_equal' => "Please give at least {$days} days notice before your move-out date.",
        ];
    }
}
