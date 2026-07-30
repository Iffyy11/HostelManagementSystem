@extends('layouts.app')

@section('page-title', 'Warden Dashboard')
@section('page-subtitle')
Block {{ auth()->user()->warden->block_assigned }} — pending approvals and room overview.
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Pending Approvals</div>
                    <div class="fs-2 fw-bold">{{ $pendingBookings->count() }}</div>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Rooms Managed</div>
                    <div class="fs-2 fw-bold">{{ $rooms->count() }}</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-door-closed"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Open Maintenance</div>
                    <div class="fs-2 fw-bold">{{ $maintenance->where('status', '!=', 'resolved')->count() }}</div>
                </div>
                <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-tools"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-modern mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Booking Approval Queue</span>
        @if($pendingBookings->count())
            <span class="badge bg-warning-subtle text-warning-emphasis">{{ $pendingBookings->count() }} pending</span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-modern mb-0 align-middle">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Room</th>
                    <th>Programme</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingBookings as $booking)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $booking->student->user->name }}</div>
                            <div class="small text-muted">{{ $booking->student->student_id_number }}</div>
                        </td>
                        <td>{{ $booking->room->displayName() }}</td>
                        <td>{{ $booking->student->programme }}</td>
                        <td>{{ $booking->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('warden.bookings.approve', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="bi bi-check-lg me-1"></i>Approve</button>
                            </form>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $booking->id }}">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                <p class="text-muted mb-0">No pending booking requests. You're all caught up!</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($pendingBookings as $booking)
    <div class="modal fade" id="rejectModal{{ $booking->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('warden.bookings.reject', $booking) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Rejecting booking for <strong>{{ $booking->student->user->name }}</strong> — {{ $booking->room->displayName() }}</p>
                    <label class="form-label fw-semibold">Reason for rejection</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Provide a clear reason the student will receive..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Booking</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="card-modern mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Move-Out Notices</span>
        @if($moveOutRequests->count())
            <span class="badge bg-info-subtle text-info-emphasis">{{ $moveOutRequests->count() }} active</span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-modern mb-0 align-middle">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Room</th>
                    <th>Move-out date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($moveOutRequests as $moveOut)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $moveOut->student->user->name }}</div>
                            <div class="small text-muted">{{ $moveOut->student->student_id_number }}</div>
                        </td>
                        <td>{{ $moveOut->booking->room->displayName() }}</td>
                        <td>{{ $moveOut->intended_move_out_date->format('M d, Y') }}</td>
                        <td><span class="badge rounded-pill {{ $moveOut->statusBadgeClass() }}">{{ $moveOut->statusLabel() }}</span></td>
                        <td class="text-end">
                            @if($moveOut->status === 'pending')
                                <form action="{{ route('warden.move-out.acknowledge', $moveOut) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-info text-white"><i class="bi bi-check2 me-1"></i>Acknowledge</button>
                                </form>
                            @endif
                            @if(in_array($moveOut->status, ['pending', 'acknowledged']))
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeMoveOut{{ $moveOut->id }}">
                                    <i class="bi bi-box-arrow-right me-1"></i>Complete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No active move-out notices.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($moveOutRequests as $moveOut)
    <div class="modal fade" id="completeMoveOut{{ $moveOut->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('warden.move-out.complete', $moveOut) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Move-Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Mark clearance complete for <strong>{{ $moveOut->student->user->name }}</strong> — {{ $moveOut->booking->room->displayName() }}.</p>
                    <p class="small">This will free the bed and notify the student.</p>
                    <label class="form-label fw-semibold">Clearance note (optional)</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="e.g. Keys returned, room inspected, no damages..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Move-Out</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header">Room Occupancy</div>
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Room</th><th>Occupancy</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($rooms as $room)
                            <tr>
                                <td class="fw-semibold">{{ $room->room_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;">
                                            <div class="progress-bar bg-success" style="width:{{ $room->capacity ? ($room->current_occupancy / $room->capacity * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="small text-muted">{{ $room->current_occupancy }}/{{ $room->capacity }}</span>
                                    </div>
                                </td>
                                <td><span class="badge rounded-pill bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $room->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header">Maintenance in Block</div>
            <div class="list-group list-group-flush">
                @forelse($maintenance as $item)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $item->room->displayName() }}</strong>
                            <span class="badge rounded-pill {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                        </div>
                        <div class="small text-muted">{{ Str::limit($item->description, 70) }}</div>
                        @if($item->status === 'open')
                            <form action="{{ route('warden.maintenance.assign', $item) }}" method="POST" class="mt-2 d-flex gap-2">
                                @csrf
                                <select name="assigned_caretaker_id" class="form-select form-select-sm" required>
                                    <option value="">Assign caretaker...</option>
                                    @foreach(\App\Models\User::role('caretaker')->get() as $caretaker)
                                        <option value="{{ $caretaker->id }}">{{ $caretaker->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-brand">Assign</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="list-group-item text-muted">No maintenance requests.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
