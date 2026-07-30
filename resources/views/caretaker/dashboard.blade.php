@extends('layouts.app')

@section('page-title', 'Caretaker Dashboard')
@section('page-subtitle', 'Manage maintenance work orders across the hostel.')

@section('content')
<div class="card-modern mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['open','in_progress','resolved'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach(['plumbing','electrical','furniture','other'] as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($requests as $request)
        <div class="col-lg-6">
            <div class="card-modern h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 text-capitalize">{{ $request->category }}</h5>
                            <div class="text-muted small">{{ $request->room->displayName() }}</div>
                        </div>
                        <span class="badge rounded-pill {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span>
                    </div>
                    <p class="mb-3">{{ $request->description }}</p>
                    <div class="small text-muted mb-3">
                        Reported by {{ $request->student->user->name }} · {{ $request->created_at->diffForHumans() }}
                    </div>
                    <form method="POST" action="{{ route('caretaker.maintenance.update', $request) }}">
                        @csrf
                        @method('PATCH')
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select name="status" class="form-select form-select-sm">
                                    @foreach(['open','in_progress','resolved'] as $status)
                                        <option value="{{ $status }}" @selected($request->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-sm btn-brand w-100">Update Status</button>
                            </div>
                            <div class="col-12">
                                <textarea name="resolution_notes" class="form-control form-control-sm" rows="2" placeholder="Resolution notes">{{ $request->resolution_notes }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="card-modern p-5 text-center text-muted">No maintenance requests match your filters.</div></div>
    @endforelse
</div>

<div class="mt-4">{{ $requests->withQueryString()->links() }}</div>
@endsection

@push('scripts')
<script>setInterval(() => location.reload(), 30000);</script>
@endpush
