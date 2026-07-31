<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use App\Models\Warden;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_rooms' => Room::count(),
            'occupied_beds' => Room::sum('current_occupancy'),
            'available_beds' => Room::get()->sum(fn ($r) => $r->availableBeds()),
            'revenue' => Payment::where('status', 'confirmed')->sum('amount'),
            'open_maintenance' => MaintenanceRequest::where('status', '!=', 'resolved')->count(),
        ];

        $occupancyByBlock = Room::selectRaw('block_name, SUM(current_occupancy) as occupied, SUM(capacity) as capacity')
            ->groupBy('block_name')
            ->get();

        $monthExpression = match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR(transaction_date, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', transaction_date)",
            default => "DATE_FORMAT(transaction_date, '%Y-%m')",
        };

        $revenueByMonth = Payment::where('status', 'confirmed')
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpression} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact('stats', 'occupancyByBlock', 'revenueByMonth'));
    }

    public function users(): View
    {
        $users = User::with('roles', 'student', 'warden')->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function createUser(): View
    {
        return view('admin.users.create');
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'student_id_number' => $request->student_id_number,
                'programme' => $request->programme,
                'phone' => $request->phone ?? '',
            ]);
        }

        if ($request->role === 'warden') {
            Warden::create([
                'user_id' => $user->id,
                'block_assigned' => $request->block_assigned,
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function toggleUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    public function reports(Request $request): View
    {
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $occupancy = Room::selectRaw('block_name, COUNT(*) as rooms, SUM(current_occupancy) as occupied, SUM(capacity) as capacity')
            ->groupBy('block_name')
            ->get();

        $revenue = Payment::where('status', 'confirmed')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->sum('amount');

        $payments = Payment::with('booking.student.user', 'booking.room')
            ->where('status', 'confirmed')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->latest('transaction_date')
            ->get();

        $maintenanceStats = MaintenanceRequest::selectRaw('category, status, COUNT(*) as count')
            ->groupBy('category', 'status')
            ->get();

        $avgResolution = MaintenanceRequest::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->avg(fn ($r) => $r->created_at->diffInHours($r->resolved_at));

        return view('admin.reports', compact(
            'from', 'to', 'occupancy', 'revenue', 'payments', 'maintenanceStats', 'avgResolution'
        ));
    }

    public function exportRevenueCsv(Request $request): StreamedResponse
    {
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $payments = Payment::with('booking.student.user')
            ->where('status', 'confirmed')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get();

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Receipt', 'Student', 'Amount', 'Phone', 'Date']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->mpesa_receipt_number,
                    $payment->booking->student->user->name ?? 'N/A',
                    $payment->amount,
                    $payment->phone_number,
                    optional($payment->transaction_date)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'revenue-report.csv');
    }

    public function exportRevenuePdf(Request $request)
    {
        $from = $request->input('from', now()->subMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $payments = Payment::with('booking.student.user')
            ->where('status', 'confirmed')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get();

        $total = $payments->sum('amount');

        $pdf = Pdf::loadView('admin.reports.revenue-pdf', compact('payments', 'from', 'to', 'total'));

        return $pdf->download('revenue-report.pdf');
    }
}
