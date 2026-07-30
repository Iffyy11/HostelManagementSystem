@extends('layouts.app')

@section('page-title', 'Maintenance Requests')
@section('page-subtitle', 'Track faults reported for your room.')

@section('topbar-actions')
    <a href="{{ route('student.maintenance.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i> New Request</a>
@endsection

@section('content')
<div class="card-modern">
    <div class="table-responsive">
        <table class="table table-modern mb-0 align-middle">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>{{ $request->room->displayName() }}</td>
                        <td class="text-capitalize">{{ $request->category }}</td>
                        <td>{{ Str::limit($request->description, 50) }}</td>
                        <td><span class="badge rounded-pill {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span></td>
                        <td>{{ $request->assignedCaretaker->name ?? '—' }}</td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No maintenance requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    setInterval(() => location.reload(), 30000);
</script>
@endpush
