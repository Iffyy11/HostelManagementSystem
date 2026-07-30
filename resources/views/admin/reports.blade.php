@extends('layouts.app')

@section('page-title', 'Reports')
@section('page-subtitle', 'Occupancy, revenue, and maintenance analytics.')

@section('content')
<div class="card-modern mb-4 p-4">
    <form class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-4">
            <button class="btn btn-brand w-100">Apply Filter</button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="text-muted small">Revenue (period)</div>
            <div class="fs-3 fw-bold text-success">KES {{ number_format($revenue) }}</div>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('admin.reports.revenue.csv', request()->query()) }}" class="btn btn-sm btn-outline-success">CSV</a>
                <a href="{{ route('admin.reports.revenue.pdf', request()->query()) }}" class="btn btn-sm btn-outline-success">PDF</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="text-muted small">Avg Resolution Time</div>
            <div class="fs-3 fw-bold">{{ $avgResolution ? round($avgResolution, 1).' hrs' : 'N/A' }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header">Occupancy by Block</div>
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Block</th><th>Rooms</th><th>Occupied</th><th>Capacity</th></tr></thead>
                    <tbody>
                        @foreach($occupancy as $row)
                            <tr>
                                <td>{{ $row->block_name }}</td>
                                <td>{{ $row->rooms }}</td>
                                <td>{{ $row->occupied }}</td>
                                <td>{{ $row->capacity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-header">Maintenance Summary</div>
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Category</th><th>Status</th><th>Count</th></tr></thead>
                    <tbody>
                        @foreach($maintenanceStats as $row)
                            <tr>
                                <td class="text-capitalize">{{ $row->category }}</td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $row->status) }}</td>
                                <td>{{ $row->count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card-modern mt-4">
    <div class="card-header">Confirmed Payments</div>
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead><tr><th>Receipt</th><th>Student</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->mpesa_receipt_number }}</td>
                        <td>{{ $payment->booking->student->user->name ?? 'N/A' }}</td>
                        <td>KES {{ number_format($payment->amount) }}</td>
                        <td>{{ optional($payment->transaction_date)->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No payments in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
