<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'directories.welcome');

Route::view('dashboard', 'directories.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'directories.profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('event-req', 'directories.event-req')
    ->middleware(['auth'])
    ->name('event-req');

Route::view('calendar', 'directories.calendar')
    ->middleware(['auth'])
    ->name('calendar');

Route::view('archive', 'directories.archive')
    ->middleware(['auth'])
    ->name('archive');

Route::view('organizations', 'directories.organizations')
    ->middleware(['auth'])
    ->name('organizations');

Route::view('reports', 'directories.reports')
    ->middleware(['auth'])
    ->name('reports');

Route::view('accounts', 'directories.accounts')
    ->middleware(['auth'])
    ->name('accounts');

// SuperAdmin routes
Route::prefix('superadmin')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::view('/', 'superadmin.dashboard')->name('superadmin.dashboard');
        Route::view('/users', 'superadmin.users')->name('superadmin.users');
        Route::view('/roles', 'superadmin.roles')->name('superadmin.roles');
        Route::view('/settings', 'superadmin.settings')->name('superadmin.settings');
        Route::view('/logs', 'superadmin.logs')->name('superadmin.logs');
        Route::view('/archive', 'superadmin.archive')->name('superadmin.archive');
        Route::view('/reports', 'superadmin.reports')->name('superadmin.reports');
    });

// Student Organization routes
Route::prefix('student-org')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::view('/', 'student-orgs.dashboard')->name('student-org.dashboard');
        Route::view('/dashboard', 'student-orgs.dashboard')->name('student-org.dashboard');
        Route::view('/submit-ticket', 'student-orgs.submit-ticket')->name('student-org.submit-ticket');
        Route::view('/my-tickets', 'student-orgs.my-tickets')->name('student-org.my-tickets');
        Route::view('/calendar', 'student-orgs.calendar')->name('student-org.calendar');
        Route::view('/reschedule', 'student-orgs.reschedule')->name('student-org.reschedule');
        Route::view('/notifications', 'student-orgs.notifications')->name('student-org.notifications');
        Route::view('/history', 'student-orgs.history')->name('student-org.history');
        Route::view('/settings', 'student-orgs.settings')->name('student-org.settings');
    });


require __DIR__.'/auth.php';
