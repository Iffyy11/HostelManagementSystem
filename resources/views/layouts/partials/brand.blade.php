@props(['compact' => false, 'light' => false])

<a {{ $attributes->merge(['class' => 'brand-lockup d-flex align-items-center gap-2 text-decoration-none ' . ($light ? 'text-white' : 'text-dark')]) }}>
    <img src="{{ asset('images/usiu-logo.png') }}" alt="USIU-Africa" class="brand-logo {{ $compact ? 'brand-logo-sm' : '' }}">
    <span class="brand-lockup-text">
        <span class="brand-lockup-title fw-bold d-block lh-sm">USIU Hostel</span>
        @unless($compact)
            <span class="brand-lockup-sub {{ $light ? 'text-white-50' : 'text-muted' }} small">Management System</span>
        @endunless
    </span>
</a>
