<?php

// OSA/admin Imports
use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Livewire\Osa\TicketReview\Show as TicketReviewShow;
use App\Livewire\Osa\TicketReview\Index as TicketReviewIndex;
use App\Livewire\Osa\TicketManagement;
use App\Livewire\Osa\EventCalendar;
use App\Livewire\Osa\Notifications as OsaNotifications;
use App\Livewire\Osa\Archive;
use App\Livewire\Osa\Reports;
use App\Livewire\Osa\Profile as OsaProfile;

// Superadmin Imports
use App\Livewire\Superadmin\Dashboard;
use App\Livewire\Superadmin\Roles\Index as RolesIndex;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use App\Livewire\Superadmin\SystemSettings\Index as SystemSettingsIndex;
use App\Livewire\Superadmin\Logs;
use App\Livewire\Superadmin\Profile as SuperadminProfile;

// Gso/Offices Imports
use App\Livewire\Gso\Dashboard as GsoDashboard;
use App\Livewire\Gso\Details as GsoDetails;
use App\Livewire\Gso\TicketReview as GsoTicketReview;
use App\Livewire\Gso\Calendar as GsoCalendar;
use App\Livewire\Gso\Approvals as GsoApprovals;
use App\Livewire\Gso\Reports as GsoReports;
use App\Http\Controllers\Gso\ReportsExportController;
use App\Livewire\Gso\Profile as GsoProfile;

// Student Org Imports
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;
use App\Livewire\StudentOrg\Calendar;
use App\Livewire\StudentOrg\MyTicket;
use App\Livewire\StudentOrg\Reschedule;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\StudentOrg\History;
use App\Livewire\StudentOrg\Notifications;
use App\Livewire\StudentOrg\Profile as StudentOrgProfile;


// Other Imports
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// If user is logged in, redirect to their role-specific dashboard
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return redirect()->route($user->getDashboardRoute());
    }

    // Otherwise, show the welcome page
    return view('osa.welcome');
});

// Profile route (accessible by all authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Generic profile route - redirects based on user role
    Route::get('/profile', function () {
        $user = Auth::user();

        // Load role relationship if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        return redirect()->route(match ($user->role?->role_name) {
            'superadmin' => 'superadmin.profile',
            'osa' => 'admin.profile',
            'gso' => 'gso.profile',
            'student-org' => 'student-org.profile',
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
        Route::get('/calendar', EventCalendar::class)->name('admin.calendar');
        Route::get('/notifications', OsaNotifications::class)->name('admin.notifications');
        Route::get('/reports', Reports::class)->name('admin.reports');
        Route::get('/archive', Archive::class)->name('admin.archive');
        Route::get('/profile', OsaProfile::class)->name('admin.profile');
    });

// SuperAdmin routes
Route::prefix('superadmin')
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('superadmin.dashboard');
        Route::get('/users', UsersIndex::class)->name('superadmin.users');
        Route::get('/roles', RolesIndex::class)->name('superadmin.roles');
        Route::get('/system-settings', SystemSettingsIndex::class)->name('superadmin.system-settings');
        Route::get('/logs', Logs::class)->name('superadmin.logs');
        Route::get('/profile', SuperadminProfile::class)->name('superadmin.profile');
    });

// GSO/Offices routes
Route::prefix('gso')
    ->middleware(['auth', 'verified', 'role:gso'])
    ->group(function () {
        Route::get('/dashboard', GsoDashboard::class)->name('gso.dashboard');
        Route::get('/ticket-review', GsoTicketReview::class)->name('gso.ticket-review');
        Route::get('/tickets/{ticket}/{office?}/{approval?}', GsoDetails::class)
            ->name('gso.ticket-details')
            ->whereNumber('office')
            ->whereNumber('approval');
        Route::get('/approvals', GsoApprovals::class)->name('gso.approvals');
        Route::get('/calendar', GsoCalendar::class)->name('gso.calendar');
        Route::view('/communication', 'gso.communication')->name('gso.communication');
        Route::get('/reports', GsoReports::class)->name('gso.reports');
        Route::get('/reports/export', [ReportsExportController::class, 'export'])
            ->name('gso.reports.export')
            ->middleware('signed');
        Route::get('/profile', GsoProfile::class)->name('gso.profile');
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
        Route::get('/profile', StudentOrgProfile::class)->name('student-org.profile');
    });

require __DIR__ . '/auth.php';
