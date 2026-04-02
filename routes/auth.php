<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Unified Login Page
    Route::livewire('login', 'pages.auth.login')
        ->name('login');

    // Keep old routes for backward compatibility, but point to the unified login
    Route::livewire('admin/login', 'pages.auth.login')
        ->name('admin.login');

    Route::livewire('student-org/login', 'pages.auth.login')
        ->name('student-org.login');

    Route::livewire('gso/login', 'pages.auth.login')
        ->name('gso.login');

    Route::livewire('superadmin/login', 'pages.auth.login')
        ->name('superadmin.login');

    Route::livewire('register', 'pages.auth.register')
        ->name('register');

    Route::livewire('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Route::livewire('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::livewire('verify-email', 'pages.auth.verify-email')
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::livewire('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Handle GET requests to /logout (e.g., when session expires and browser redirects)
    Route::get('logout', function () {
        return redirect()->route('login');
    })->name('logout.get');
});
