@extends('layouts.app')

@section('title', 'Student Registration')
@section('body-class', 'auth-body')

@section('content')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-lg-6">
            <div class="text-center text-white mb-4">
                <img src="{{ asset('images/usiu-logo.png') }}" alt="USIU-Africa" class="auth-logo mb-3">
                <h1 class="h3 fw-bold">Student Registration</h1>
                <p class="opacity-75">Use your institutional email to create an account.</p>
            </div>
            <div class="auth-card p-4 p-md-5">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student ID</label>
                            <input type="text" name="student_id_number" value="{{ old('student_id_number') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Institutional Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="you@students.usiu.ac.ke" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Programme</label>
                            <input type="text" name="programme" value="{{ old('programme') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone (M-Pesa)</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="2547XXXXXXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-brand btn-lg w-100 mt-4">Create Account</button>
                </form>
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}">Already registered? Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
