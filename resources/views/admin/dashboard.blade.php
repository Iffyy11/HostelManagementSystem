@extends('layouts.app')

@section('page-title', 'Admin Overview')
@section('page-subtitle', 'Occupancy, revenue, and operational health at a glance.')

@section('content')
<div class="row g-4 mb-4">
    @foreach([
        ['label' => 'Total Rooms', 'value' => $stats['total_rooms'], 'icon' => 'building', 'class' => 'bg-primary-subtle text-primary'],
        ['label' => 'Occupied Beds', 'value' => $stats['occupied_beds'], 'icon' => 'people-fill', 'class' => 'bg-success-subtle text-success'],
        ['label' => 'Available Beds', 'value' => $stats['available_beds'], 'icon' => 'door-open', 'class' => 'bg-info-subtle text-info'],
        ['label' => 'Total Revenue', 'value' => 'KES '.number_format($stats['revenue']), 'icon' => 'cash-stack', 'class' => 'bg-warning-subtle text-warning'],
    ] as $stat)
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                        <div class="fs-4 fw-bold">{{ $stat['value'] }}</div>
                    </div>
                    <div class="stat-icon {{ $stat['class'] }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Occupancy by Block</h5>
            <canvas id="occupancyChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Revenue Trend (6 months)</h5>
            <canvas id="revenueChart" height="220"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const occupancyCtx = document.getElementById('occupancyChart');
new Chart(occupancyCtx, {
    type: 'bar',
    data: {
        labels: @json($occupancyByBlock->pluck('block_name')),
        datasets: [
            { label: 'Occupied Beds', data: @json($occupancyByBlock->pluck('occupied')), backgroundColor: '#003B71' },
            { label: 'Capacity', data: @json($occupancyByBlock->pluck('capacity')), backgroundColor: '#cbd5e1' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($revenueByMonth->pluck('month')),
        datasets: [{
            label: 'Revenue (KES)',
            data: @json($revenueByMonth->pluck('total')),
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,.15)',
            fill: true,
            tension: .35
        }]
    },
    options: { responsive: true }
});
</script>
@endpush
