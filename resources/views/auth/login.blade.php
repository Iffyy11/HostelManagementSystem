@extends('layouts.app')

@section('title', 'Sign In')
@section('body-class', 'auth-body')

@section('content')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center text-white mb-4">
                <img src="{{ asset('images/usiu-logo.png') }}" alt="USIU-Africa" class="auth-logo mb-3">
                <div class="hero-badge mb-3"><i class="bi bi-building"></i> USIU Hostel Portal</div>
                <h1 class="h3 fw-bold">Welcome back</h1>
                <p class="opacity-75 mb-0">Sign in to manage bookings, payments, and maintenance.</p>
            </div>
            <div class="auth-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="stat-icon bg-brand-subtle text-brand mx-auto mb-3"><i class="bi bi-box-arrow-in-right"></i></div>
                    <h2 class="h5 fw-bold mb-0">Sign in to your account</h2>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-brand btn-lg w-100">Sign In</button>
                </form>
                <div class="text-center mt-4">
                    <span class="text-muted">New student?</span>
                    <a href="{{ route('register') }}" class="fw-semibold">Create account</a>
                </div>
            </div>
            <div class="text-center mt-4 text-white-50 small">
                Demo: admin@usiu.ac.ke / password
            </div>
        </div>
    </div>
</div>
@endsection
