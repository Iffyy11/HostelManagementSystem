@extends('layouts.app')

@section('page-title', 'Student Dashboard')
@section('page-subtitle', 'Track bookings, payments, and maintenance in one place.')

@section('topbar-actions')
    <a href="{{ route('student.rooms') }}" class="btn btn-brand">
        <i class="bi bi-door-open me-1"></i> Browse Rooms
    </a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Active Bookings</div>
                    <div class="fs-2 fw-bold">{{ $bookings->whereIn('status', ['pending','approved_by_warden','awaiting_payment','confirmed'])->count() }}</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-calendar-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Booking Fee</div>
                    <div class="fs-4 fw-bold">KES {{ number_format(config('hostel.booking_fee')) }}</div>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Maintenance Requests</div>
                    <div class="fs-2 fw-bold">{{ $maintenance->count() }}</div>
                </div>
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-tools"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>My Bookings</span>
            </div>
            <div class="table-responsive">
                <table class="table table-modern mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="fw-semibold">{{ $booking->room->displayName() }}</td>
                                <td>{{ $booking->date_booked->format('M d, Y') }}</td>
                                <td><span class="badge rounded-pill {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                                <td class="text-end">
                                    @if(in_array($booking->status, ['approved_by_warden', 'awaiting_payment']))
                                        <a href="{{ route('student.bookings.pay', $booking) }}" class="btn btn-sm btn-brand">Pay Now</a>
                                    @endif
                                    @if($booking->status === 'confirmed')
                                        <a href="{{ route('student.move-out.index') }}" class="btn btn-sm btn-outline-brand">Move Out</a>
                                    @endif
                                    @if($booking->isCancellableByStudent())
                                        <form action="{{ route('student.bookings.cancel', $booking) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No bookings yet. Browse available rooms to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header">Recent Maintenance</div>
            <div class="list-group list-group-flush">
                @forelse($maintenance as $item)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong class="text-capitalize">{{ $item->category }}</strong>
                            <span class="badge rounded-pill {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span>
                        </div>
                        <div class="small text-muted">{{ Str::limit($item->description, 60) }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No maintenance requests yet.</div>
                @endforelse
            </div>
            <div class="p-3">
                <a href="{{ route('student.maintenance.create') }}" class="btn btn-outline-success w-100">Report an Issue</a>
            </div>
        </div>
    </div>
</div>
@endsection
