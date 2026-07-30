@extends('layouts.app')

@section('title', 'Housing & Accommodation — USIU-Africa')
@section('body-class', 'landing-body')

@section('content')
<div class="landing-topbar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 small">
        <span><i class="bi bi-envelope me-1"></i> housing@usiu.ac.ke</span>
        <span><i class="bi bi-telephone me-1"></i> +254 730 116 265</span>
        <span class="text-brand-gold fw-semibold"><i class="bi bi-lightning-charge me-1"></i> Book online — no more physical queues</span>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-light landing-nav sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            @include('layouts.partials.brand', ['compact' => true])
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-brand">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-brand">Apply for a Room</a>
        </div>
    </div>
</nav>

<section class="hero-section hero-usiu d-flex align-items-center position-relative">
    <div class="container py-5 position-relative" style="z-index:1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <p class="hero-eyebrow mb-3">USIU-Africa · Housing & Accommodation</p>
                <h1 class="display-4 fw-bold mb-4 lh-sm">A home away from home — now bookable online.</h1>
                <p class="lead opacity-90 mb-4">
                    Quality housing services in a clean, safe and inclusive living-learning environment.
                    Browse rooms, submit your application, pay via M-Pesa, and track maintenance — without visiting the housing office.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4 fw-semibold text-dark shadow">
                        <i class="bi bi-door-open me-2"></i>Apply for a Room
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Staff Portal</a>
                </div>
                <div class="hero-trust d-flex flex-wrap gap-3 small opacity-90">
                    <span><i class="bi bi-check-circle-fill text-warning me-1"></i> 258 on-campus beds</span>
                    <span><i class="bi bi-check-circle-fill text-warning me-1"></i> M-Pesa STK Push</span>
                    <span><i class="bi bi-check-circle-fill text-warning me-1"></i> Warden approvals</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-gallery">
                    <img src="{{ asset('images/hostel-building.png') }}" alt="USIU-Africa hostel building" class="hero-gallery-main rounded-4 shadow-lg">
                    <div class="hero-gallery-badge card-modern p-3 shadow">
                        <div class="text-muted small">From</div>
                        <div class="fs-3 fw-bold text-brand">KES 30,000</div>
                        <div class="small text-muted">On-campus per semester*</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-2">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="landing-stat">
                    <div class="fs-2 fw-bold text-brand">{{ $stats['blocks'] }}</div>
                    <div class="text-muted small">Hostel Blocks</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="landing-stat">
                    <div class="fs-2 fw-bold text-brand">{{ $stats['rooms'] }}</div>
                    <div class="text-muted small">Furnished Rooms</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="landing-stat">
                    <div class="fs-2 fw-bold text-brand-gold">{{ $stats['available_beds'] }}</div>
                    <div class="text-muted small">Beds Available Now</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="landing-stat">
                    <div class="fs-2 fw-bold text-brand">24/7</div>
                    <div class="text-muted small">Secure Access</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light border-top border-bottom">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="{{ asset('images/hostel-building.png') }}" alt="USIU-Africa on-campus housing" class="img-fluid rounded-4 shadow-lg w-100 section-photo">
            </div>
            <div class="col-lg-6">
                <p class="section-eyebrow">On-Campus Housing</p>
                <h2 class="fw-bold mb-3">Live steps away from class.</h2>
                <p class="text-muted mb-4">
                    Two residential buildings accommodate students close to instructional buildings, dining, and university services.
                    Facilities include TV rooms, indoor games, study rooms, and resident assistants on every wing.
                </p>
                <ul class="list-unstyled landing-checklist mb-4">
                    <li><i class="bi bi-check2-circle text-brand-gold me-2"></i>Fully furnished twin rooms</li>
                    <li><i class="bi bi-check2-circle text-brand-gold me-2"></i>Communal bathrooms on each floor</li>
                    <li><i class="bi bi-check2-circle text-brand-gold me-2"></i>Priority for international students</li>
                    <li><i class="bi bi-check2-circle text-brand-gold me-2"></i>Resident assistants on every wing</li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn-brand">Start Room Application</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <p class="section-eyebrow justify-content-center">Why this portal?</p>
            <h2 class="fw-bold mb-2">Skip the queue. Book from anywhere.</h2>
            <p class="text-muted mx-auto" style="max-width:640px;">
                Previously, students filled paper Room Application Forms at the Admissions Office.
                This system digitizes the entire journey — matching USIU housing policy with modern convenience.
            </p>
        </div>
        <div class="row g-4">
            @foreach([
                ['old' => 'Paper application forms', 'new' => 'Online room application', 'icon' => 'file-earmark-text'],
                ['old' => 'Visit housing office to check availability', 'new' => 'Live bed availability by block', 'icon' => 'grid-3x3-gap'],
                ['old' => 'Manual payment follow-up', 'new' => 'Instant M-Pesa STK Push payment', 'icon' => 'phone'],
                ['old' => 'Walk-in maintenance requests', 'new' => 'Track repairs from your dashboard', 'icon' => 'tools'],
            ] as $compare)
                <div class="col-md-6">
                    <div class="compare-card h-100">
                        <div class="compare-old"><i class="bi bi-x-circle me-2"></i>{{ $compare['old'] }}</div>
                        <div class="compare-new"><i class="bi bi-{{ $compare['icon'] }} me-2"></i>{{ $compare['new'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5 bg-brand-gradient text-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How online booking works</h2>
            <p class="opacity-75">Four steps from application to move-in.</p>
        </div>
        <div class="workflow-steps">
            @foreach([
                ['icon' => 'search', 'title' => 'Browse Rooms', 'text' => 'View blocks, capacity, and real-time availability'],
                ['icon' => 'send', 'title' => 'Apply Online', 'text' => 'Submit your room request in minutes'],
                ['icon' => 'check2-circle', 'title' => 'Get Approved', 'text' => 'Your warden reviews and approves the booking'],
                ['icon' => 'phone', 'title' => 'Pay & Move In', 'text' => 'Complete payment via M-Pesa STK Push'],
            ] as $step)
                <div class="workflow-step workflow-step-light">
                    <div class="workflow-icon workflow-icon-gold"><i class="bi bi-{{ $step['icon'] }}"></i></div>
                    <h6 class="text-white">{{ $step['title'] }}</h6>
                    <p class="opacity-75">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <p class="section-eyebrow justify-content-center">Amenities</p>
            <h2 class="fw-bold">Everything you need on campus</h2>
            <p class="text-muted mx-auto mt-2" style="max-width:640px;">
                On-campus rooms come fully furnished so you can move in and focus on your studies from day one.
            </p>
        </div>
        <div class="row g-4">
            @foreach([
                ['icon' => 'bed', 'title' => 'Bed & Mattress', 'text' => 'Each room has twin beds with quality mattresses — shared double occupancy so you and your roommate both have a comfortable place to rest.'],
                ['icon' => 'book', 'title' => 'Study Desk & Chair', 'text' => 'A dedicated desk and chair in every room gives you a private study spot without leaving your wing.'],
                ['icon' => 'cabinet-fill', 'title' => 'Wardrobe & Shelf', 'text' => 'Built-in wardrobe space and shelving keep clothes, books, and personal items organized in a compact room layout.'],
                ['icon' => 'building', 'title' => 'On-Campus Blocks', 'text' => 'Two residential buildings sit steps from classrooms, the library, dining, and other university services.'],
                ['icon' => 'people', 'title' => 'Community Living', 'text' => 'TV rooms, indoor games, study areas, and resident assistants on every wing help you settle in and meet other students.'],
                ['icon' => 'shield-check', 'title' => '24/7 Security', 'text' => 'Controlled access, warden oversight, and round-the-clock security so students live in a safe, supervised environment.'],
            ] as $amenity)
                <div class="col-md-6 col-lg-4">
                    <div class="amenity-card amenity-card-text h-100 p-4">
                        <div class="stat-icon bg-gold-subtle text-brand-gold mb-3"><i class="bi bi-{{ $amenity['icon'] }}"></i></div>
                        <h5 class="fw-bold mb-2">{{ $amenity['title'] }}</h5>
                        <p class="text-muted small mb-0">{{ $amenity['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row g-5 align-items-start">
            <div class="col-lg-5">
                <p class="section-eyebrow">FAQ</p>
                <h2 class="fw-bold mb-3">Common housing questions</h2>
                <p class="text-muted">Answers aligned with official USIU-Africa housing policy.</p>
                <div class="contact-card p-4 mt-4">
                    <h6 class="fw-bold mb-2">Housing Office</h6>
                    <p class="mb-1 small"><i class="bi bi-envelope me-2 text-brand"></i>housing@usiu.ac.ke</p>
                    <p class="mb-1 small"><i class="bi bi-telephone me-2 text-brand"></i>+254 730 116 265</p>
                    <p class="mb-0 small text-muted">New Block Hostel, Ground Floor</p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="accordion faq-accordion" id="housingFaq">
                    @foreach([
                        ['q' => 'Who can apply for on-campus accommodation?', 'a' => 'All USIU-Africa students enrolled full-time (minimum 9 credit units for undergraduates, 6 for graduates).'],
                        ['q' => 'How much does on-campus housing cost?', 'a' => 'On-campus accommodation is KES 30,000 per semester. Booking fees may apply when reserving through this portal.'],
                        ['q' => 'Are rooms single or shared?', 'a' => 'All on-campus rooms are double occupancy (twin beds), fully furnished with study tables, chairs, and wardrobes.'],
                        ['q' => 'How do I pay after approval?', 'a' => 'Once your warden approves the booking, pay securely via M-Pesa STK Push directly from your student dashboard.'],
                        ['q' => 'What if on-campus space is full?', 'a' => 'USIU also accredits off-campus hostels such as Qwetu, Kisima, and RCTI. Contact the Housing Office for recommendations.'],
                    ] as $i => $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h2>
                            <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#housingFaq">
                                <div class="accordion-body text-muted">{{ $faq['a'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-2">
        <div class="cta-section p-5 text-center position-relative">
            <div class="position-relative" style="z-index:1;">
                <h2 class="fw-bold mb-3">Ready to secure your room?</h2>
                <p class="opacity-90 mb-4 mx-auto" style="max-width:560px;">
                    Join USIU-Africa students using the digital hostel portal — apply, pay, and manage your accommodation in one place.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-5 shadow fw-semibold text-dark">
                        Create Student Account
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Staff Sign In</a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="landing-footer py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                @include('layouts.partials.brand', ['compact' => true, 'light' => true])
                <p class="small opacity-75 mt-3 mb-0">Education to take you places. Digitizing USIU-Africa housing for students and staff.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <div class="d-grid gap-2 small">
                    <a href="{{ route('register') }}" class="footer-link">Student Registration</a>
                    <a href="{{ route('login') }}" class="footer-link">Staff Login</a>
                    <a href="https://www.usiu.ac.ke/student-life/housing-accommodation/" target="_blank" rel="noopener" class="footer-link">Official USIU Housing Page</a>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Contact</h6>
                <p class="small mb-1">housing@usiu.ac.ke</p>
                <p class="small mb-1">+254 730 116 265</p>
                <p class="small opacity-75">© {{ date('Y') }} USIU-Africa — Final Year Project</p>
            </div>
        </div>
    </div>
</footer>
@endsection
