@extends('layouts.app')

@section('page-title', 'Move Out')
@section('page-subtitle')
Notify housing management and follow the clearance steps before vacating your room.
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-modern mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Steps before you move out</span>
                <span class="badge bg-brand-subtle text-brand">{{ $noticeDays }}-day notice required</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Complete each step below before your intended move-out date.
                    Submit your notice through this portal so your warden and the housing office can prepare your clearance.
                </p>
                <ol class="move-out-steps list-unstyled mb-0">
                    @foreach($steps as $i => $step)
                        <li class="move-out-step d-flex gap-3 {{ $loop->last ? '' : 'mb-4 pb-4 border-bottom' }}">
                            <div class="move-out-step-number">{{ $i + 1 }}</div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $step['title'] }}</h6>
                                <p class="text-muted small mb-0">{{ $step['description'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        @if($pastRequests->isNotEmpty())
            <div class="card-modern">
                <div class="card-header">Previous move-out notices</div>
                <div class="list-group list-group-flush">
                    @foreach($pastRequests as $request)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $request->intended_move_out_date->format('M d, Y') }}</div>
                                    @if($request->reason)
                                        <div class="small text-muted">{{ Str::limit($request->reason, 80) }}</div>
                                    @endif
                                </div>
                                <span class="badge rounded-pill {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3">Your current room</h6>
            <div class="d-flex align-items-center gap-3 mb-0">
                <div class="stat-icon bg-brand-subtle text-brand"><i class="bi bi-door-closed"></i></div>
                <div>
                    <div class="fw-bold">{{ $booking->room->displayName() }}</div>
                    <div class="small text-muted">Confirmed since {{ $booking->date_booked->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        @if($activeRequest)
            <div class="card-modern p-4 border-start border-4 border-info">
                <h6 class="fw-bold mb-2">Active move-out notice</h6>
                <p class="mb-2">
                    <span class="badge rounded-pill {{ $activeRequest->statusBadgeClass() }}">{{ $activeRequest->statusLabel() }}</span>
                </p>
                <dl class="small mb-4">
                    <dt class="text-muted">Intended move-out date</dt>
                    <dd class="fw-semibold">{{ $activeRequest->intended_move_out_date->format('l, M d, Y') }}</dd>
                    @if($activeRequest->reason)
                        <dt class="text-muted">Your note</dt>
                        <dd>{{ $activeRequest->reason }}</dd>
                    @endif
                    @if($activeRequest->staff_note)
                        <dt class="text-muted">Housing office response</dt>
                        <dd>{{ $activeRequest->staff_note }}</dd>
                    @endif
                    <dt class="text-muted">Submitted</dt>
                    <dd>{{ $activeRequest->created_at->format('M d, Y g:i A') }}</dd>
                </dl>
                @if($activeRequest->status === 'pending')
                    <p class="small text-muted mb-3">Your warden will review this notice. Continue following the steps on the left.</p>
                @else
                    <p class="small text-muted mb-3">Your notice was acknowledged. Complete the remaining clearance steps before your move-out date.</p>
                @endif
                <form action="{{ route('student.move-out.cancel', $activeRequest) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this move-out notice?')">
                        Cancel notice
                    </button>
                </form>
            </div>
        @else
            <div class="card-modern p-4 p-md-5">
                <h6 class="fw-bold mb-2">Notify management</h6>
                <p class="text-muted small mb-4">
                    Submit your intended move-out date. Housing management and your warden will be notified automatically.
                </p>
                <form method="POST" action="{{ route('student.move-out.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Intended move-out date</label>
                        <input type="date" name="intended_move_out_date" class="form-control" min="{{ $earliestDate }}" value="{{ old('intended_move_out_date') }}" required>
                        <div class="form-text">Earliest date: {{ \Carbon\Carbon::parse($earliestDate)->format('M d, Y') }} ({{ $noticeDays }} days from today).</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Reason (optional)</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="e.g. End of semester, transfer, graduation...">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-send me-1"></i> Submit move-out notice
                    </button>
                </form>
            </div>
        @endif

        <div class="contact-card p-4 mt-4">
            <h6 class="fw-bold mb-2">Need help?</h6>
            <p class="small mb-1"><i class="bi bi-envelope me-2 text-brand"></i>housing@usiu.ac.ke</p>
            <p class="small mb-0"><i class="bi bi-telephone me-2 text-brand"></i>+254 730 116 265</p>
        </div>
    </div>
</div>
@endsection
