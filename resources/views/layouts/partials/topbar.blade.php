<header class="app-topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
        <div>
        <h1 class="page-title mb-0">@yield('page-title', 'Dashboard')</h1>
        @hasSection('page-subtitle')
            <p class="page-subtitle mb-0">@yield('page-subtitle')</p>
        @endif
        </div>
    </div>
    <div class="topbar-actions">
        @yield('topbar-actions')
    </div>
</header>
