<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Caretaker\DashboardController as CaretakerDashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Warden\DashboardController as WardenDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/mpesa/callback', MpesaCallbackController::class)->name('mpesa.callback');

Route::middleware(['auth'])->group(function () {
    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/rooms', [StudentDashboardController::class, 'rooms'])->name('rooms');
        Route::post('/bookings', [StudentDashboardController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings/{booking}/pay', [StudentDashboardController::class, 'showPayment'])->name('bookings.pay');
        Route::post('/bookings/{booking}/pay', [StudentDashboardController::class, 'initiatePayment'])->name('bookings.pay.initiate');
        Route::post('/bookings/{booking}/cancel', [StudentDashboardController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::get('/maintenance', [StudentDashboardController::class, 'maintenanceIndex'])->name('maintenance.index');
        Route::get('/maintenance/create', [StudentDashboardController::class, 'maintenanceCreate'])->name('maintenance.create');
        Route::post('/maintenance', [StudentDashboardController::class, 'maintenanceStore'])->name('maintenance.store');
        Route::get('/move-out', [StudentDashboardController::class, 'moveOutIndex'])->name('move-out.index');
        Route::post('/move-out', [StudentDashboardController::class, 'moveOutStore'])->name('move-out.store');
        Route::post('/move-out/{moveOutRequest}/cancel', [StudentDashboardController::class, 'moveOutCancel'])->name('move-out.cancel');
    });

    Route::prefix('warden')->middleware('role:warden')->name('warden.')->group(function () {
        Route::get('/dashboard', [WardenDashboardController::class, 'index'])->name('dashboard');
        Route::post('/bookings/{booking}/approve', [WardenDashboardController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [WardenDashboardController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/cancel', [WardenDashboardController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/move-out/{moveOutRequest}/acknowledge', [WardenDashboardController::class, 'acknowledgeMoveOut'])->name('move-out.acknowledge');
        Route::post('/move-out/{moveOutRequest}/complete', [WardenDashboardController::class, 'completeMoveOut'])->name('move-out.complete');
        Route::post('/maintenance/{maintenance}/assign', [WardenDashboardController::class, 'assignMaintenance'])->name('maintenance.assign');
    });

    Route::prefix('caretaker')->middleware('role:caretaker')->name('caretaker.')->group(function () {
        Route::get('/dashboard', [CaretakerDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/maintenance/{maintenance}', [CaretakerDashboardController::class, 'update'])->name('maintenance.update');
    });

    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
        Route::get('/users/create', [AdminDashboardController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
        Route::patch('/users/{user}/toggle', [AdminDashboardController::class, 'toggleUser'])->name('users.toggle');
        Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/revenue/csv', [AdminDashboardController::class, 'exportRevenueCsv'])->name('reports.revenue.csv');
        Route::get('/reports/revenue/pdf', [AdminDashboardController::class, 'exportRevenuePdf'])->name('reports.revenue.pdf');
    });
});
