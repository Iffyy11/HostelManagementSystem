<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, BookingService $bookingService)
    {
        try {
            Log::info('M-Pesa callback received', $request->all());

            $body = $request->input('Body.stkCallback', []);
            $resultCode = $body['ResultCode'] ?? null;
            $checkoutId = $body['CheckoutRequestID'] ?? null;

            if (! $checkoutId) {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            $payment = Payment::where('checkout_request_id', $checkoutId)->first();

            if (! $payment) {
                Log::warning('Payment not found for checkout ID', ['checkout_id' => $checkoutId]);

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            if ((int) $resultCode === 0) {
                $items = collect($body['CallbackMetadata']['Item'] ?? []);
                $receipt = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? 'UNKNOWN';

                $bookingService->confirmPayment($payment, (string) $receipt);
            } else {
                $bookingService->failPayment($payment);
            }
        } catch (\Throwable $e) {
            Log::error('M-Pesa callback error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
