<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/usiu-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/hostel.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('body-class')">
    @auth
        <div class="sidebar-overlay"></div>
        <div class="app-shell">
            @include('layouts.partials.sidebar')
            <div class="app-main">
                @include('layouts.partials.topbar')
                <main class="app-content container-fluid py-4">
                    @include('layouts.partials.alerts')
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        @include('layouts.partials.alerts')
        @yield('content')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/hostel.js') }}"></script>
    @stack('scripts')
</body>
</html>
