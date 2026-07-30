<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(?string $phone, string $message): void
    {
        if (empty($phone)) {
            return;
        }

        // Stub for Africa's Talking or similar — logs in dev, swappable for production.
        Log::info('SMS stub sent', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
