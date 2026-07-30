<?php

namespace App\Http\Controllers\Warden;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookingRequest;
use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\MoveOutRequest;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\MoveOutService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $warden = auth()->user()->warden;
        $pendingBookings = Booking::with('student.user', 'room')
            ->whereHas('room', fn ($q) => $q->where('block_name', $warden->block_assigned))
            ->where('status', 'pending')
            ->latest()
            ->get();

        $rooms = Room::where('block_name', $warden->block_assigned)->get();
        $maintenance = MaintenanceRequest::with('student.user', 'room')
            ->whereHas('room', fn ($q) => $q->where('block_name', $warden->block_assigned))
            ->latest()
            ->take(10)
            ->get();

        $moveOutRequests = MoveOutRequest::with('student.user', 'booking.room')
            ->whereHas('booking.room', fn ($q) => $q->where('block_name', $warden->block_assigned))
            ->whereIn('status', ['pending', 'acknowledged'])
            ->latest()
            ->get();

        return view('warden.dashboard', compact('pendingBookings', 'rooms', 'maintenance', 'moveOutRequests'));
    }

    public function approve(Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $this->authorizeBlock($booking);
        $bookingService->approve($booking);

        return back()->with('success', 'Booking approved. Student notified to pay.');
    }

    public function reject(RejectBookingRequest $request, Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $this->authorizeBlock($booking);
        $bookingService->reject($booking, $request->rejection_reason);

        return back()->with('success', 'Booking rejected.');
    }

    public function cancelBooking(Request $request, Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $this->authorizeBlock($booking);
        $request->validate(['note' => ['required', 'string', 'max:1000']]);
        $bookingService->cancelByStaff($booking, $request->note);

        return back()->with('success', 'Booking cancelled.');
    }

    public function assignMaintenance(Request $request, MaintenanceRequest $maintenance, NotificationService $notifications): RedirectResponse
    {
        $request->validate([
            'assigned_caretaker_id' => ['required', 'exists:users,id'],
        ]);

        $maintenance->update([
            'assigned_caretaker_id' => $request->assigned_caretaker_id,
            'status' => 'in_progress',
        ]);

        $caretaker = \App\Models\User::find($request->assigned_caretaker_id);
        if ($caretaker) {
            $notifications->notify($caretaker, 'Maintenance Assigned', "You were assigned a {$maintenance->category} request.");
            $notifications->notify(
                $maintenance->student->user,
                'Maintenance Update',
                'Your maintenance request is now in progress.'
            );
        }

        return back()->with('success', 'Caretaker assigned.');
    }

    public function acknowledgeMoveOut(Request $request, MoveOutRequest $moveOutRequest, MoveOutService $moveOutService): RedirectResponse
    {
        $this->authorizeMoveOutBlock($moveOutRequest);
        $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        try {
            $moveOutService->acknowledge($moveOutRequest, $request->note);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Move-out notice acknowledged. Student notified.');
    }

    public function completeMoveOut(Request $request, MoveOutRequest $moveOutRequest, MoveOutService $moveOutService): RedirectResponse
    {
        $this->authorizeMoveOutBlock($moveOutRequest);
        $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        try {
            $moveOutService->complete($moveOutRequest, $request->note);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Move-out completed. Room freed and student notified.');
    }

    protected function authorizeBlock(Booking $booking): void
    {
        $block = auth()->user()->warden->block_assigned;
        if ($booking->room->block_name !== $block) {
            abort(403);
        }
    }

    protected function authorizeMoveOutBlock(MoveOutRequest $moveOutRequest): void
    {
        $block = auth()->user()->warden->block_assigned;
        if ($moveOutRequest->booking->room->block_name !== $block) {
            abort(403);
        }
    }
}
