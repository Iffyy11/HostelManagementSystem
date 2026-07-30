@extends('layouts.app')

@section('page-title', 'Complete Payment')
@section('page-subtitle', 'Pay hostel booking fee via M-Pesa STK Push.')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card-modern p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="stat-icon bg-brand-subtle text-brand mx-auto mb-3"><i class="bi bi-phone"></i></div>
                <h4 class="fw-bold">M-Pesa Payment</h4>
                <p class="text-muted mb-0">{{ $booking->room->displayName() }}</p>
            </div>
            <div class="bg-light rounded-4 p-4 mb-4 text-center">
                <div class="text-muted small">Amount Due</div>
                <div class="display-6 fw-bold text-brand">KES {{ number_format(config('hostel.booking_fee')) }}</div>
            </div>
            @if(config('mpesa.demo_any_phone') && config('mpesa.environment') !== 'production')
                <div class="alert alert-info small mb-0">
                    <strong>Demo mode:</strong> enter any valid Kenyan M-Pesa number (07XX or 2547XX). Payment completes instantly for testing.
                </div>
            @elseif(config('mpesa.environment') === 'sandbox')
                <div class="alert alert-warning small mb-0">
                    <strong>Sandbox:</strong> real phones usually won't get a prompt. Use production Go Live for any number, or enable <code>MPESA_DEMO_ANY_PHONE=true</code> in <code>.env</code>.
                </div>
            @else
                <div class="alert alert-success small mb-0">
                    <strong>Live M-Pesa:</strong> enter your number — you'll receive an STK prompt on your phone.
                </div>
            @endif
            <form method="POST" action="{{ route('student.bookings.pay.initiate', $booking) }}" class="mt-3">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold">M-Pesa Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="form-control form-control-lg" placeholder="2547XXXXXXXX" required>
                    <div class="form-text">You will receive an STK push prompt on this number.</div>
                </div>
                <button type="submit" class="btn btn-brand btn-lg w-100">
                    <i class="bi bi-send me-2"></i>Send STK Push
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
