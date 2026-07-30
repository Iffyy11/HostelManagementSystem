<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MoveOutRequest;
use App\Models\Room;
use App\Models\Warden;
use Illuminate\Support\Facades\DB;

class MoveOutService
{
    public function __construct(
        protected NotificationService $notifications,
        protected BookingService $bookingService,
    ) {}

    public function submit(Booking $booking, string $intendedDate, ?string $reason = null): MoveOutRequest
    {
        if ($booking->status !== 'confirmed') {
            throw new \RuntimeException('Only confirmed bookings can submit a move-out notice.');
        }

        $existing = MoveOutRequest::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'acknowledged'])
            ->exists();

        if ($existing) {
            throw new \RuntimeException('You already have an active move-out request for this room.');
        }

        $request = MoveOutRequest::create([
            'student_id' => $booking->student_id,
            'booking_id' => $booking->id,
            'intended_move_out_date' => $intendedDate,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        $room = $booking->room;
        $studentName = $booking->student->user->name;
        $date = $request->intended_move_out_date->format('M d, Y');

        $warden = Warden::where('block_assigned', $room->block_name)->first();
        if ($warden?->user) {
            $this->notifications->notify(
                $warden->user,
                'Move-Out Notice',
                "{$studentName} intends to vacate {$room->displayName()} on {$date}."
            );
        }

        return $request;
    }

    public function acknowledge(MoveOutRequest $moveOutRequest, ?string $note = null): void
    {
        if ($moveOutRequest->status !== 'pending') {
            throw new \RuntimeException('Only pending move-out requests can be acknowledged.');
        }

        $moveOutRequest->update([
            'status' => 'acknowledged',
            'staff_note' => $note,
            'acknowledged_at' => now(),
        ]);

        $this->notifications->notify(
            $moveOutRequest->student->user,
            'Move-Out Acknowledged',
            "Your move-out notice for {$moveOutRequest->booking->room->displayName()} was received. Please complete the clearance steps before {$moveOutRequest->intended_move_out_date->format('M d, Y')}."
        );
    }

    public function complete(MoveOutRequest $moveOutRequest, ?string $note = null): void
    {
        if (! in_array($moveOutRequest->status, ['pending', 'acknowledged'], true)) {
            throw new \RuntimeException('This move-out request cannot be completed.');
        }

        DB::transaction(function () use ($moveOutRequest, $note) {
            $booking = $moveOutRequest->booking;

            $moveOutRequest->update([
                'status' => 'completed',
                'staff_note' => $note ?? $moveOutRequest->staff_note,
                'completed_at' => now(),
            ]);

            if ($booking->status === 'confirmed') {
                $room = Room::lockForUpdate()->find($booking->room_id);
                if ($room && $room->current_occupancy > 0) {
                    $room->decrement('current_occupancy');
                    if ($room->status === 'full' && $room->current_occupancy < $room->capacity) {
                        $room->update(['status' => 'available']);
                    }
                }

                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_note' => 'Move-out completed on '.$moveOutRequest->completed_at->format('M d, Y'),
                ]);
            }

            $this->notifications->notify(
                $moveOutRequest->student->user,
                'Move-Out Complete',
                "Your clearance for {$booking->room->displayName()} is complete. Thank you for staying with USIU housing."
            );
        });
    }

    public function cancelByStudent(MoveOutRequest $moveOutRequest): void
    {
        if (! $moveOutRequest->isActive()) {
            throw new \RuntimeException('This move-out request cannot be cancelled.');
        }

        $moveOutRequest->update(['status' => 'cancelled']);
    }
}
