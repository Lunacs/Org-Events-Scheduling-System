<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::middleware('guest')->group(function () {
    // Unified Login Page
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // Keep old routes for backward compatibility, but point to the unified login
    Volt::route('admin/login', 'pages.auth.login')
        ->name('admin.login');

    Volt::route('student-org/login', 'pages.auth.login')
        ->name('student-org.login');

    Volt::route('gso/login', 'pages.auth.login')
        ->name('gso.login');

    Volt::route('superadmin/login', 'pages.auth.login')
        ->name('superadmin.login');

    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Handle GET requests to /logout (e.g., when session expires and browser redirects)
    Route::get('logout', function () {
        return redirect()->route('login');
    })->name('logout.get');
});
