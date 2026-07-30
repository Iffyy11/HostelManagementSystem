<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $phone = app(\App\Services\MpesaService::class)->formatPhone((string) $this->input('phone'));

            if (! app(\App\Services\MpesaService::class)->isValidKenyanMobile($phone)) {
                $validator->errors()->add('phone', 'Enter a valid Kenyan M-Pesa number (e.g. 0712345678 or 254712345678).');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (is_array($data) && isset($data['phone'])) {
            $data['phone'] = app(\App\Services\MpesaService::class)->formatPhone($data['phone']);
        }

        return $data;
    }
}
