<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    // OSA/Admin Login
    Volt::route('admin/login', 'pages.auth.osa-login')
        ->name('admin.login');

    // Student Organization Login
    Volt::route('student-org/login', 'pages.auth.student-org-login')
        ->name('student-org.login');

    // GSO/Offices Login
    Volt::route('gso/login', 'pages.auth.gso-login')
        ->name('gso.login');

    // SuperAdmin Login
    Volt::route('superadmin/login', 'pages.auth.superadmin-login')
        ->name('superadmin.login');

    // Default login redirects to OSA login
    Volt::route('login', 'pages.auth.osa-login')
        ->name('login');

    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
