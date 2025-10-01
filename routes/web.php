<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'osa.welcome');

// Profile route (accessible by all authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/profile', 'profile')->name('profile');

    // Generic dashboard route - redirects based on user role
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return redirect()->route($user->getDashboardRoute());
    })->name('dashboard');
});

// OSA routes
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'role:osa'])
    ->group(function () {
        Route::view('/dashboard', 'osa.dashboard')->name('admin.dashboard');
        Route::view('/event-req', 'osa.event-req')->name('admin.event-req');
        Route::view('/calendar', 'osa.calendar')->name('admin.calendar');
        Route::view('/archive', 'osa.archive')->name('admin.archive');
        Route::view('/student-organizations', 'osa.organizations')->name('admin.organizations');
        Route::view('/reports', 'osa.reports')->name('admin.reports');
        Route::view('/accounts', 'osa.accounts')->name('admin.accounts');
        Route::view('/profile', 'osa.profile')->name('admin.profile');
    });

// SuperAdmin routes
Route::prefix('superadmin')
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->group(function () {
        Route::view('/dashboard', 'superadmin.dashboard')->name('superadmin.dashboard');
        Route::view('/users', 'superadmin.users')->name('superadmin.users');
        Route::view('/roles', 'superadmin.roles')->name('superadmin.roles');
        Route::view('/settings', 'superadmin.settings')->name('superadmin.settings');
        Route::view('/logs', 'superadmin.logs')->name('superadmin.logs');
        Route::view('/archive', 'superadmin.archive')->name('superadmin.archive');
        Route::view('/reports', 'superadmin.reports')->name('superadmin.reports');
    });

// GSO/Offices routes
Route::prefix('gso')
    ->middleware(['auth', 'verified', 'role:gso'])
    ->group(function () {
        Route::view('/dashboard', 'gso.dashboard')->name('gso.dashboard');
        Route::view('/ticket-review', 'gso.ticket-review')->name('gso.ticket-review');
        Route::view('/approvals', 'gso.approvals')->name('gso.approvals');
        Route::view('/calendar', 'gso.calendar')->name('gso.calendar');
        Route::view('/communication', 'gso.communication')->name('gso.communication');
        Route::view('/reports', 'gso.reports')->name('gso.reports');
        Route::view('/profile', 'gso.profile')->name('gso.profile');
    });

// Student Organization routes
Route::prefix('student-org')
    ->middleware(['auth', 'verified', 'role:student_org'])
    ->group(function () {
        Route::view('/dashboard', 'student-orgs.dashboard')->name('student-org.dashboard');
        Route::view('/submit-ticket', 'student-orgs.submit-ticket')->name('student-org.submit-ticket');
        Route::view('/my-tickets', 'student-orgs.my-tickets')->name('student-org.my-tickets');
        Route::view('/calendar', 'student-orgs.calendar')->name('student-org.calendar');
        Route::view('/reschedule', 'student-orgs.reschedule')->name('student-org.reschedule');
        Route::view('/notifications', 'student-orgs.notifications')->name('student-org.notifications');
        Route::view('/history', 'student-orgs.history')->name('student-org.history');
        Route::view('/settings', 'student-orgs.settings')->name('student-org.settings');
    });


require __DIR__ . '/auth.php';
