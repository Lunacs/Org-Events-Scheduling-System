<?php

// Public Pages
use App\Livewire\AboutUs;
use App\Livewire\Faq;

// OSA/admin Imports
use App\Livewire\Osa\Archive;
use App\Livewire\Osa\Reports;
use App\Livewire\Superadmin\Logs;
use App\Livewire\Osa\EventCalendar;
use App\Livewire\StudentOrg\History;
use Illuminate\Support\Facades\Auth;
use App\Livewire\StudentOrg\Calendar;
use App\Livewire\StudentOrg\MyTicket;
// Superadmin Imports
use Illuminate\Support\Facades\Route;
use App\Livewire\Osa\TicketManagement;
use App\Livewire\Superadmin\Dashboard;
use App\Livewire\StudentOrg\Reschedule;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\StudentOrg\TicketDetails;
use App\Livewire\StudentOrg\Notifications;
// Gso/Offices Imports
use App\Livewire\Gso\Details as GsoDetails;
use App\Livewire\Gso\Profile as GsoProfile;
use App\Livewire\Gso\Reports as GsoReports;
use App\Livewire\Osa\Profile as OsaProfile;
use App\Livewire\Gso\Calendar as GsoCalendar;
use App\Livewire\Gso\Dashboard as GsoDashboard;
use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Http\Controllers\Gso\ReportsExportController;
// Student Org Imports
use App\Livewire\Gso\TicketReview as GsoTicketReview;
use App\Livewire\Superadmin\Roles\Index as RolesIndex;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use App\Livewire\Gso\Notifications as GsoNotifications;
use App\Livewire\Osa\Notifications as OsaNotifications;
use App\Livewire\StudentOrg\Profile as StudentOrgProfile;
use App\Livewire\Superadmin\Profile as SuperadminProfile;
use App\Livewire\Osa\TicketReview\Show as TicketReviewShow;
// Other Imports
use App\Livewire\Osa\TicketReview\Index as TicketReviewIndex;
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;
use App\Livewire\Superadmin\SystemSettings\Index as SystemSettingsIndex;

// Redirect to login page, or to user's dashboard if already authenticated
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return redirect()->route($user->getDashboardRoute());
    }

    // Redirect to login page
    return redirect()->route('login');
});

// About Us - Public route
Route::get('/about-us', AboutUs::class)->name('about-us');

// FAQ - Public route
Route::get('/faq', Faq::class)->name('faq');

// Profile route (accessible by all authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Generic profile route - redirects based on user role
    Route::get('/profile', function () {
        $user = Auth::user();

        // Load role relationship if not already loaded
        if (! $user->relationLoaded('role')) {
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

    // Keep-alive endpoint to refresh session
    Route::post('/keep-alive', function () {
        // Simply touching the session updates the last_activity timestamp
        session()->put('last_activity_refresh', now()->timestamp);

        return response()->json([
            'success' => true,
            'message' => 'Session refreshed',
            'timestamp' => now()->toDateTimeString(),
        ]);
    })->name('keep-alive');

    // Signed URL routes for attachment access (private storage)
    Route::get('/attachments/{attachment}/preview', [\App\Http\Controllers\AttachmentController::class, 'preview'])
        ->name('attachments.preview')
        ->middleware('signed');
    Route::get('/attachments/{attachment}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])
        ->name('attachments.download')
        ->middleware('signed');

    // Email change verification routes (signed URLs)
    Route::get('/email/verify-new/{id}/{hash}', [\App\Http\Controllers\VerifyNewEmailController::class, 'verify'])
        ->name('email.verify-new')
        ->middleware('signed');
    Route::get('/email/cancel-change/{id}/{hash}', [\App\Http\Controllers\VerifyNewEmailController::class, 'cancel'])
        ->name('email.cancel-change')
        ->middleware('signed');
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
        Route::get('/calendar', \App\Livewire\Superadmin\Calendar\Index::class)->name('superadmin.calendar');
        Route::get('/tickets', \App\Livewire\Superadmin\Tickets\Index::class)->name('superadmin.tickets');
        Route::get('/archive', \App\Livewire\Superadmin\Archive\Index::class)->name('superadmin.archive');
        Route::get('/reports', \App\Livewire\Superadmin\Reports\Index::class)->name('superadmin.reports');
        Route::get('/notifications', \App\Livewire\Superadmin\Notifications::class)->name('superadmin.notifications');
        Route::get('/system-settings', SystemSettingsIndex::class)->name('superadmin.system-settings');
        Route::get('/system-settings/content/create', \App\Livewire\Superadmin\SystemSettings\ContentSectionEditor::class)->name('superadmin.content-section.create');
        Route::get('/system-settings/content/{id}/edit', \App\Livewire\Superadmin\SystemSettings\ContentSectionEditor::class)->name('superadmin.content-section.edit');
        Route::get('/system-settings/event-types/create', \App\Livewire\Superadmin\SystemSettings\EventTypeEditor::class)->name('superadmin.event-type.create');
        Route::get('/system-settings/event-types/{id}/edit', \App\Livewire\Superadmin\SystemSettings\EventTypeEditor::class)->name('superadmin.event-type.edit');
        Route::get('/system-settings/venues/create', \App\Livewire\Superadmin\SystemSettings\VenueEditor::class)->name('superadmin.venue.create');
        Route::get('/system-settings/venues/{id}/edit', \App\Livewire\Superadmin\SystemSettings\VenueEditor::class)->name('superadmin.venue.edit');
        Route::get('/faqs', \App\Livewire\Superadmin\FaqManager::class)->name('superadmin.faqs');
        Route::get('/faqs/create', \App\Livewire\Superadmin\FaqEditor::class)->name('superadmin.faq.create');
        Route::get('/faqs/{id}/edit', \App\Livewire\Superadmin\FaqEditor::class)->name('superadmin.faq.edit');
        Route::get('/admin-tools', \App\Livewire\Superadmin\AdminTools\Index::class)->name('superadmin.admin-tools');
        Route::get('/logs', Logs::class)->name('superadmin.logs');
        Route::get('/profile', SuperadminProfile::class)->name('superadmin.profile');
    });

// GSO/Offices routes
Route::prefix('gso')
    ->middleware(['auth', 'verified', 'role:gso'])
    ->group(function () {
        Route::get('/dashboard', GsoDashboard::class)->name('gso.dashboard');
        Route::get('/ticket-review', GsoTicketReview::class)->name('gso.ticket-review');
        Route::get('/tickets/{ticketNumber}', GsoDetails::class)
            ->name('gso.ticket-details');
        Route::get('/calendar', GsoCalendar::class)->name('gso.calendar');
        Route::get('/notifications', GsoNotifications::class)->name('gso.notifications');
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
        Route::get('/tickets/{ticketNumber}', TicketDetails::class)->name('student-org.ticket-details');
        Route::get('/calendar', Calendar::class)->name('student-org.calendar');
        Route::get('/reschedule', Reschedule::class)->name('student-org.reschedule');
        Route::get('/notifications', Notifications::class)->name('student-org.notifications');
        Route::get('/history', History::class)->name('student-org.history');
        Route::get('/profile', StudentOrgProfile::class)->name('student-org.profile');
    });

require __DIR__ . '/auth.php';
