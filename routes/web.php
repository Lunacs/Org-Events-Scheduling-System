<?php

use App\Livewire\StudentOrg\History;
use App\Livewire\Superadmin\Logs;
use App\Livewire\Superadmin\Roles;
use App\Livewire\Superadmin\Users;
use Illuminate\Support\Facades\Auth;
use App\Livewire\StudentOrg\Calendar;
use App\Livewire\StudentOrg\MyTicket;
use Illuminate\Support\Facades\Route;
use App\Livewire\Superadmin\Dashboard;
use App\Livewire\StudentOrg\Reschedule;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\StudentOrg\Notifications;
use App\Livewire\Superadmin\SystemSettings;
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;

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
// Route::prefix('superadmin')
//     ->middleware(['auth', 'verified', 'role:superadmin'])
//     ->group(function () {
//         Route::view('/dashboard', 'livewire.superadmin.dashboard')->name('superadmin.dashboard');
//         Route::view('/users', 'superadmin.users')->name('superadmin.users');
//         Route::view('/roles', 'superadmin.roles')->name('superadmin.roles');
//         Route::view('/settings', 'superadmin.settings')->name('superadmin.settings');
//         Route::view('/logs', 'superadmin.logs')->name('superadmin.logs');
//         Route::view('/archive', 'superadmin.archive')->name('superadmin.archive');
//         Route::view('/reports', 'superadmin.reports')->name('superadmin.reports');
//     });
Route::prefix('superadmin')
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('superadmin.dashboard');
        Route::get('/users', Users::class)->name('superadmin.users');
        Route::get('/roles', Roles::class)->name('superadmin.roles');
        Route::get('/system-settings', SystemSettings::class)->name('superadmin.system-settings');
        Route::get('/logs', Logs::class)->name('superadmin.logs');
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
        Route::get('/dashboard', StudentOrgDashboard::class)->name('student-org.dashboard');
        Route::get('/submit-ticket', SubmitTicket::class)->name('student-org.submit-ticket');
        Route::get('/my-tickets', MyTicket::class)->name('student-org.my-tickets');
        Route::get('/calendar', Calendar::class)->name('student-org.calendar');
        Route::get('/reschedule', Reschedule::class)->name('student-org.reschedule');
        Route::get('/notifications', Notifications::class)->name('student-org.notifications');
        Route::get('/history', History::class)->name('student-org.history');
        Route::view('/profile', 'student-orgs.profile')->name('student-org.profile');
        Route::view('/settings', 'student-orgs.settings')->name('student-org.settings');
    });


require __DIR__ . '/auth.php';
