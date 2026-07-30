@extends('layouts.app')

@section('page-title', 'Available Rooms')
@section('page-subtitle', 'Real-time availability across all hostel blocks.')

@section('content')
<div class="row g-4">
    @forelse($rooms as $room)
        <div class="col-md-6 col-xl-4">
            <div class="room-card">
                <div class="room-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">{{ $room->block_name }}</div>
                        <div class="fs-5 fw-bold">Room {{ $room->room_number }}</div>
                    </div>
                    <span class="badge bg-light text-dark">{{ $room->availableBeds() }} bed(s) free</span>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Capacity</span>
                        <strong>{{ $room->capacity }} students</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Occupied</span>
                        <strong>{{ $room->current_occupancy }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted">Status</span>
                        <span class="badge rounded-pill {{ $room->hasAvailableSpace() ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                            {{ ucfirst(str_replace('_', ' ', $room->status)) }}
                        </span>
                    </div>
                    @if($room->hasAvailableSpace())
                        <form action="{{ route('student.bookings.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->id }}">
                            <button type="submit" class="btn btn-brand w-100" onclick="return confirm('Submit booking request for this room?')">
                                Request Booking
                            </button>
                        </form>
                    @else
                        <button class="btn btn-secondary w-100" disabled>Not Available</button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card-modern p-5 text-center text-muted">No rooms available at the moment.</div>
        </div>
    @endforelse
</div>
@endsection
