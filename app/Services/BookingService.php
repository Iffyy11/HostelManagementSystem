<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    public function createBookingRequest(int $studentId, int $roomId): Booking
    {
        return DB::transaction(function () use ($studentId, $roomId) {
            $room = Room::lockForUpdate()->findOrFail($roomId);

            if (! $room->hasAvailableSpace()) {
                throw new \RuntimeException('This room is no longer available.');
            }

            $existing = Booking::where('student_id', $studentId)
                ->whereIn('status', ['pending', 'approved_by_warden', 'awaiting_payment', 'confirmed'])
                ->exists();

            if ($existing) {
                throw new \RuntimeException('You already have an active booking request.');
            }

            $booking = Booking::create([
                'student_id' => $studentId,
                'room_id' => $room->id,
                'date_booked' => now()->toDateString(),
                'status' => 'pending',
            ]);

            $warden = \App\Models\Warden::where('block_assigned', $room->block_name)->first();
            if ($warden?->user) {
                $this->notifications->notify(
                    $warden->user,
                    'New Booking Request',
                    "Student {$booking->student->user->name} requested {$room->displayName()}."
                );
            }

            return $booking;
        });
    }

    public function approve(Booking $booking): void
    {
        $booking->update(['status' => 'approved_by_warden']);

        $this->notifications->notify(
            $booking->student->user,
            'Booking Approved',
            "Your booking for {$booking->room->displayName()} was approved. Please complete M-Pesa payment."
        );
    }

    public function reject(Booking $booking, string $reason): void
    {
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $this->notifications->notify(
            $booking->student->user,
            'Booking Rejected',
            "Your booking for {$booking->room->displayName()} was rejected. Reason: {$reason}"
        );
    }

    public function preparePayment(Booking $booking, string $phone): Payment
    {
        $booking->update(['status' => 'awaiting_payment']);

        return Payment::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => config('hostel.booking_fee'),
                'phone_number' => $phone,
                'status' => 'pending',
            ]
        );
    }

    public function confirmPayment(Payment $payment, string $receiptNumber): void
    {
        DB::transaction(function () use ($payment, $receiptNumber) {
            $room = Room::lockForUpdate()->findOrFail($payment->booking->room_id);

            $payment->update([
                'status' => 'confirmed',
                'mpesa_receipt_number' => $receiptNumber,
                'transaction_date' => now(),
            ]);

            $payment->booking->update(['status' => 'confirmed']);

            $room->increment('current_occupancy');
            if ($room->current_occupancy >= $room->capacity) {
                $room->update(['status' => 'full']);
            }

            $studentUser = $payment->booking->student->user;
            $this->notifications->notify(
                $studentUser,
                'Payment Confirmed',
                "Your hostel booking for {$room->displayName()} is confirmed. Receipt: {$receiptNumber}"
            );

            $warden = \App\Models\Warden::where('block_assigned', $room->block_name)->first();
            if ($warden?->user) {
                $this->notifications->notify(
                    $warden->user,
                    'Booking Confirmed',
                    "Payment received for {$room->displayName()} — student {$studentUser->name}."
                );
            }
        });
    }

    public function failPayment(Payment $payment): void
    {
        try {
            $payment->update(['status' => 'failed']);
            $payment->booking->update(['status' => 'awaiting_payment']);

            $this->notifications->notify(
                $payment->booking->student->user,
                'Payment Failed',
                'Your M-Pesa payment was not completed. You may retry payment.'
            );
        } catch (\Throwable $e) {
            Log::error('Payment failure handling error', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
        }
    }

    public function cancelByStudent(Booking $booking, ?string $note = null): void
    {
        if (! $booking->isCancellableByStudent()) {
            throw new \RuntimeException('This booking cannot be cancelled.');
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_note' => $note,
        ]);
    }

    public function cancelByStaff(Booking $booking, string $note): void
    {
        DB::transaction(function () use ($booking, $note) {
            if ($booking->status === 'confirmed') {
                $room = Room::lockForUpdate()->find($booking->room_id);
                if ($room && $room->current_occupancy > 0) {
                    $room->decrement('current_occupancy');
                    if ($room->status === 'full' && $room->current_occupancy < $room->capacity) {
                        $room->update(['status' => 'available']);
                    }
                }
            }

            $booking->update([
                'status' => 'cancelled',
                'cancellation_note' => $note,
            ]);

            $this->notifications->notify(
                $booking->student->user,
                'Booking Cancelled',
                "Your booking for {$booking->room->displayName()} was cancelled. Note: {$note}"
            );
        });
    }
}
