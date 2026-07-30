<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    public function getAccessToken(): ?string
    {
        return Cache::remember('mpesa_access_token', 3500, function () {
            $response = Http::withBasicAuth(
                config('mpesa.consumer_key'),
                config('mpesa.consumer_secret')
            )->get(config('mpesa.base_url').'/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);

            if (! $response->successful()) {
                Log::error('M-Pesa OAuth failed', ['body' => $response->body()]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    public function credentialsConfigured(): bool
    {
        $passkey = config('mpesa.passkey');

        return filled(config('mpesa.consumer_key'))
            && filled(config('mpesa.consumer_secret'))
            && filled($passkey)
            && ! in_array(strtoupper((string) $passkey), ['N/A', 'NA', 'NONE'], true);
    }

    public function isProduction(): bool
    {
        return config('mpesa.environment') === 'production';
    }

    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '254'.substr($phone, 1);
        }

        if (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            return '254'.$phone;
        }

        return $phone;
    }

    public function isValidKenyanMobile(string $phone): bool
    {
        $phone = $this->formatPhone($phone);

        return (bool) preg_match('/^254(7\d{8}|1\d{8})$/', $phone);
    }

    public function initiateStkPush(string $phone, float $amount, int $bookingId): array
    {
        if (! $this->credentialsConfigured()) {
            return [
                'success' => false,
                'message' => 'M-Pesa passkey is missing. Copy the Lipa Na M-Pesa Online passkey from developer.safaricom.co.ke into MPESA_PASSKEY.',
            ];
        }

        $token = $this->getAccessToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'M-Pesa authentication failed. Check MPESA_CONSUMER_KEY and MPESA_CONSUMER_SECRET in .env.',
            ];
        }

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password = base64_encode(config('mpesa.shortcode').config('mpesa.passkey').$timestamp);
        $phone = $this->formatPhone($phone);

        $response = Http::withToken($token)->post(
            config('mpesa.base_url').'/mpesa/stkpush/v1/processrequest',
            [
                'BusinessShortCode' => config('mpesa.shortcode'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) ceil($amount),
                'PartyA' => $phone,
                'PartyB' => config('mpesa.shortcode'),
                'PhoneNumber' => $phone,
                'CallBackURL' => config('mpesa.callback_url'),
                'AccountReference' => 'BOOKING-'.$bookingId,
                'TransactionDesc' => 'Hostel booking fee',
            ]
        );

        $data = $response->json() ?? [];

        if (! $response->successful() || ($data['errorCode'] ?? null)) {
            Log::error('STK Push failed', ['body' => $response->body()]);

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'STK Push request failed.',
            ];
        }

        if (($data['ResponseCode'] ?? null) !== '0') {
            return [
                'success' => false,
                'message' => $data['ResponseDescription'] ?? 'STK Push was rejected by M-Pesa.',
            ];
        }

        return [
            'success' => true,
            'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $data['MerchantRequestID'] ?? null,
            'message' => $this->isProduction()
                ? ($data['CustomerMessage'] ?? 'STK Push sent. Check your phone.')
                : ($data['CustomerMessage'] ?? 'STK Push sent. Check the Daraja simulator or your test phone.'),
        ];
    }
}
