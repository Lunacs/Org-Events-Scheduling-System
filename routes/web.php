<?php

<<<<<<< HEAD
use App\Livewire\Osa\Approvals;
use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Livewire\Osa\EventCalendar;
use App\Livewire\Osa\Profile as OsaProfile;
use App\Livewire\Osa\Reports;
use App\Livewire\Osa\TicketManagement;
use App\Livewire\Osa\TicketReview\Index as TicketReviewIndex;
use App\Livewire\Osa\TicketReview\Show as TicketReviewShow;
=======
use App\Livewire\Gso\Approvals as GsoApprovals;
use App\Livewire\Gso\Calendar as GsoCalendar;
use App\Livewire\Gso\Dashboard as GsoDashboard;
use App\Livewire\Gso\Details as GsoDetails;
use App\Livewire\Gso\Reports as GsoReports;
use App\Livewire\Gso\TicketReview as GsoTicketReview;
>>>>>>> origin/gso-dashboard_functionalities
use App\Livewire\StudentOrg\Calendar;
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;
use App\Livewire\StudentOrg\History;
use App\Livewire\StudentOrg\MyTicket;
use App\Livewire\StudentOrg\Notifications;
use App\Livewire\StudentOrg\Reschedule;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\Superadmin\Dashboard;
use App\Livewire\Superadmin\Logs;
use App\Livewire\Superadmin\Roles\Index as RolesIndex;
use App\Livewire\Superadmin\SystemSettings\Index as SystemSettingsIndex;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'osa.welcome');

// Profile route (accessible by all authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Generic profile route - redirects based on user role
    Route::get('/profile', function () {
        $user = Auth::user();

        return redirect()->route(match ($user->role_id) {
            \App\Models\User::ROLE_SUPERADMIN => 'superadmin.profile',
            \App\Models\User::ROLE_OSA => 'admin.profile',
            \App\Models\User::ROLE_GSO => 'gso.profile',
            \App\Models\User::ROLE_STUDENT_ORG => 'student-org.profile',
            default => 'admin.profile',
        });
    })->name('profile');

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
        Route::get('/dashboard', OsaDashboard::class)->name('admin.dashboard');
        Route::get('/tickets', TicketManagement::class)->name('admin.ticket');
        Route::get('/ticket-review', TicketReviewIndex::class)->name('osa.ticket-review.index');
        Route::get('/ticket-review/{ticketNumber}', TicketReviewShow::class)->name('osa.ticket-review.show');
        Route::get('/approvals', Approvals::class)->name('admin.approvals');
        Route::get('/calendar', EventCalendar::class)->name('admin.calendar');
        //        Route::view('/student-organizations', 'osa.organizations')->name('admin.organizations');
        Route::get('/reports', Reports::class)->name('admin.reports');
        Route::view('/accounts', 'osa.accounts')->name('admin.accounts');
        Route::get('/profile', OsaProfile::class)->name('admin.profile');
    });

// SuperAdmin routes
Route::prefix('superadmin')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('superadmin.dashboard');
        Route::get('/users', UsersIndex::class)->name('superadmin.users');
        Route::get('/roles', RolesIndex::class)->name('superadmin.roles');
        Route::get('/system-settings', SystemSettingsIndex::class)->name('superadmin.system-settings');
        Route::get('/logs', Logs::class)->name('superadmin.logs');
        Route::get('/profile', \App\Livewire\Superadmin\Profile::class)->name('superadmin.profile');
    });

// GSO/Offices routes
Route::prefix('gso')
    ->group(function () {
        Route::get('/dashboard', GsoDashboard::class)->name('gso.dashboard');
        Route::get('/ticket-review', GsoTicketReview::class)->name('gso.ticket-review');
        Route::get('/tickets/{ticket}', GsoDetails::class)->name('gso.ticket-details');
        Route::get('/approvals', GsoApprovals::class)->name('gso.approvals');
        Route::get('/calendar', GsoCalendar::class)->name('gso.calendar');
        Route::view('/communication', 'gso.communication')->name('gso.communication');
<<<<<<< HEAD
        Route::view('/reports', 'gso.reports')->name('gso.reports');
        Route::get('/profile', \App\Livewire\Gso\Profile::class)->name('gso.profile');
=======
    Route::get('/reports', GsoReports::class)->name('gso.reports');
        Route::view('/profile', 'gso.profile')->name('gso.profile');
>>>>>>> origin/gso-dashboard_functionalities
    });

// Student Organization routes
Route::prefix('student-org')
    ->group(function () {
        Route::get('/dashboard', StudentOrgDashboard::class)->name('student-org.dashboard');
        Route::get('/submit-ticket', SubmitTicket::class)->name('student-org.submit-ticket');
        Route::get('/my-tickets', MyTicket::class)->name('student-org.my-tickets');
        Route::get('/calendar', Calendar::class)->name('student-org.calendar');
        Route::get('/reschedule', Reschedule::class)->name('student-org.reschedule');
        Route::get('/notifications', Notifications::class)->name('student-org.notifications');
        Route::get('/history', History::class)->name('student-org.history');
        Route::get('/profile', \App\Livewire\StudentOrg\Profile::class)->name('student-org.profile');
    });

require __DIR__.'/auth.php';
