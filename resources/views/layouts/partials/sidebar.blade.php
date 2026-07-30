@php
    $role = auth()->user()->getRoleNames()->first();
    $nav = match($role) {
        'student' => [
            ['route' => 'student.dashboard', 'icon' => 'speedometer2', 'label' => 'Dashboard'],
            ['route' => 'student.rooms', 'icon' => 'door-open', 'label' => 'Browse Rooms'],
            ['route' => 'student.maintenance.index', 'icon' => 'tools', 'label' => 'Maintenance'],
            ['route' => 'student.move-out.index', 'icon' => 'box-arrow-right', 'label' => 'Move Out'],
        ],
        'warden' => [
            ['route' => 'warden.dashboard', 'icon' => 'clipboard-check', 'label' => 'Approvals'],
        ],
        'caretaker' => [
            ['route' => 'caretaker.dashboard', 'icon' => 'wrench-adjustable', 'label' => 'Work Orders'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'icon' => 'grid', 'label' => 'Overview'],
            ['route' => 'admin.users.index', 'icon' => 'people', 'label' => 'Users'],
            ['route' => 'admin.reports', 'icon' => 'bar-chart', 'label' => 'Reports'],
        ],
        default => [],
    };
@endphp

<aside class="app-sidebar">
    <div class="sidebar-brand">
        @include('layouts.partials.brand', ['compact' => true, 'light' => true, 'class' => 'text-white'])
        <div class="ms-1">
            <div class="brand-sub">{{ ucfirst($role) }} Portal</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        @foreach($nav as $item)
            <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs(str_replace('.dashboard', '.*', $item['route'])) || request()->routeIs($item['route']) ? 'active' : '' }}">
                <i class="bi bi-{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst($role) }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm w-100 mt-3">
                <i class="bi bi-box-arrow-right me-1"></i> Sign out
            </button>
        </form>
    </div>
</aside>
