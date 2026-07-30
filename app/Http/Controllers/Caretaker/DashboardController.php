<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Models\MaintenanceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaintenanceRequest::with('student.user', 'room', 'assignedCaretaker');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $requests = $query->latest()->paginate(15);

        return view('caretaker.dashboard', compact('requests'));
    }

    public function update(UpdateMaintenanceRequest $request, MaintenanceRequest $maintenance, NotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();

        if (($data['status'] ?? null) === 'resolved') {
            $data['resolved_at'] = now();
        }

        $maintenance->update($data);

        $notifications->notify(
            $maintenance->student->user,
            'Maintenance Update',
            "Your maintenance request status is now: {$maintenance->statusLabel()}."
        );

        return back()->with('success', 'Request updated.');
    }
}
