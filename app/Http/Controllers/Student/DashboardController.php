<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\StoreMoveOutRequest;
use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\MoveOutRequest;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\MoveOutService;
use App\Services\MpesaService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;
        $bookings = $student->bookings()->with('room', 'payment')->latest()->get();
        $maintenance = $student->maintenanceRequests()->with('room')->latest()->take(5)->get();

        return view('student.dashboard', compact('bookings', 'maintenance'));
    }

    public function rooms(): View
    {
        $rooms = Room::query()
            ->where('status', '!=', 'under_maintenance')
            ->orderBy('block_name')
            ->orderBy('room_number')
            ->get();

        return view('student.rooms', compact('rooms'));
    }

    public function storeBooking(StoreBookingRequest $request, BookingService $bookingService): RedirectResponse
    {
        try {
            $bookingService->createBookingRequest(
                auth()->user()->student->id,
                (int) $request->room_id
            );

            return redirect()->route('student.dashboard')->with('success', 'Booking request submitted. Await warden approval.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function showPayment(Booking $booking): View|RedirectResponse
    {
        $this->authorizeBooking($booking);

        if (! in_array($booking->status, ['approved_by_warden', 'awaiting_payment'], true)) {
            return redirect()->route('student.dashboard')->with('error', 'Payment is not available for this booking.');
        }

        return view('student.payment', compact('booking'));
    }

    public function initiatePayment(InitiatePaymentRequest $request, Booking $booking, BookingService $bookingService, MpesaService $mpesa): RedirectResponse
    {
        $this->authorizeBooking($booking);

        $payment = $bookingService->preparePayment($booking, $request->phone);
        $phone = $mpesa->formatPhone($request->phone);

        if (config('mpesa.demo_any_phone') && ! $mpesa->isProduction()) {
            $bookingService->confirmPayment($payment, 'DEMO'.strtoupper(Str::random(8)));

            return redirect()->route('student.dashboard')->with(
                'success',
                "Payment completed for {$phone}. Sandbox demo mode — switch to production Go Live for real STK prompts on any phone."
            );
        }

        if (app()->environment('local') && ! $mpesa->credentialsConfigured()) {
            $bookingService->confirmPayment($payment, 'DEMO'.strtoupper(Str::random(8)));

            return redirect()->route('student.dashboard')->with(
                'success',
                'Demo payment completed. Add M-Pesa sandbox credentials in .env for live STK Push.'
            );
        }

        $result = $mpesa->initiateStkPush($phone, (float) $payment->amount, $booking->id);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        $payment->update(['checkout_request_id' => $result['checkout_request_id']]);

        return redirect()->route('student.dashboard')->with('success', $result['message']);
    }

    public function cancelBooking(Request $request, Booking $booking, BookingService $bookingService): RedirectResponse
    {
        $this->authorizeBooking($booking);

        try {
            $bookingService->cancelByStudent($booking, $request->input('note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Booking cancelled.');
    }

    public function maintenanceIndex(): View
    {
        $requests = auth()->user()->student
            ->maintenanceRequests()
            ->with('room', 'assignedCaretaker')
            ->latest()
            ->get();

        return view('student.maintenance.index', compact('requests'));
    }

    public function maintenanceCreate(): View|RedirectResponse
    {
        $booking = auth()->user()->student->confirmedBooking();

        if (! $booking) {
            return redirect()->route('student.maintenance.index')
                ->with('error', 'You need a confirmed room booking to log maintenance requests.');
        }

        return view('student.maintenance.create', compact('booking'));
    }

    public function maintenanceStore(StoreMaintenanceRequest $request, NotificationService $notifications): RedirectResponse
    {
        $booking = auth()->user()->student->confirmedBooking();

        if (! $booking) {
            return redirect()->route('student.maintenance.index')->with('error', 'No confirmed booking found.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('maintenance', 'public');
        }

        $maintenance = MaintenanceRequest::create([
            'student_id' => auth()->user()->student->id,
            'room_id' => $booking->room_id,
            'description' => $request->description,
            'category' => $request->category,
            'photo_path' => $photoPath,
            'status' => 'open',
        ]);

        $caretakers = \App\Models\User::role('caretaker')->get();
        foreach ($caretakers as $caretaker) {
            $notifications->notify(
                $caretaker,
                'New Maintenance Request',
                "New {$maintenance->category} issue in {$booking->room->displayName()}."
            );
        }

        return redirect()->route('student.maintenance.index')->with('success', 'Maintenance request submitted.');
    }

    public function moveOutIndex(): View|RedirectResponse
    {
        $booking = auth()->user()->student->confirmedBooking();

        if (! $booking) {
            return redirect()->route('student.dashboard')
                ->with('error', 'You need a confirmed room booking to submit a move-out notice.');
        }

        $activeRequest = MoveOutRequest::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'acknowledged'])
            ->latest()
            ->first();

        $pastRequests = MoveOutRequest::where('student_id', auth()->user()->student->id)
            ->where('booking_id', $booking->id)
            ->whereNotIn('status', ['pending', 'acknowledged'])
            ->latest()
            ->get();

        $steps = config('hostel.move_out_steps');
        $noticeDays = config('hostel.move_out_notice_days');
        $earliestDate = now()->addDays($noticeDays)->format('Y-m-d');

        return view('student.move-out.index', compact('booking', 'activeRequest', 'pastRequests', 'steps', 'noticeDays', 'earliestDate'));
    }

    public function moveOutStore(StoreMoveOutRequest $request, MoveOutService $moveOutService): RedirectResponse
    {
        $booking = auth()->user()->student->confirmedBooking();

        if (! $booking) {
            return redirect()->route('student.dashboard')->with('error', 'No confirmed booking found.');
        }

        try {
            $moveOutService->submit(
                $booking,
                $request->intended_move_out_date,
                $request->reason
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('student.move-out.index')->with('success', 'Move-out notice submitted. Housing management has been notified.');
    }

    public function moveOutCancel(MoveOutRequest $moveOutRequest, MoveOutService $moveOutService): RedirectResponse
    {
        if ($moveOutRequest->student_id !== auth()->user()->student->id) {
            abort(403);
        }

        try {
            $moveOutService->cancelByStudent($moveOutRequest);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Move-out notice cancelled.');
    }

    protected function authorizeBooking(Booking $booking): void
    {
        if ($booking->student_id !== auth()->user()->student->id) {
            abort(403);
        }
    }
}
