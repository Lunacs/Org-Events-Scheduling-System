<?php

// Public Pages
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DraftAttachmentController;
use App\Http\Controllers\Gso\ReportsExportController;
// OSA/admin Imports
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Controllers\VerifyNewEmailController;
use App\Livewire\AboutUs;
use App\Livewire\DataPrivacyNotice;
use App\Livewire\Faq;
use App\Livewire\Gso\Calendar as GsoCalendar;
use App\Livewire\Gso\Dashboard as GsoDashboard;
use App\Livewire\Gso\Details as GsoDetails;
use App\Livewire\Gso\Notifications as GsoNotifications;
// Superadmin Imports
use App\Livewire\Gso\Profile as GsoProfile;
use App\Livewire\Gso\Reports as GsoReports;
use App\Livewire\Gso\TicketReview as GsoTicketReview;
use App\Livewire\Osa\Archive;
use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Livewire\Osa\EventCalendar;
use App\Livewire\Osa\Notifications as OsaNotifications;
// Gso/Offices Imports
use App\Livewire\Osa\Profile as OsaProfile;
use App\Livewire\Osa\Reports;
use App\Livewire\Osa\TicketManagement;
use App\Livewire\Osa\TicketReview\Index as TicketReviewIndex;
use App\Livewire\Osa\TicketReview\Show as TicketReviewShow;
use App\Livewire\StudentOrg\Calendar;
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;
use App\Livewire\StudentOrg\History;
// Student Org Imports
use App\Livewire\StudentOrg\MyTicket;
use App\Livewire\StudentOrg\Notifications;
use App\Livewire\StudentOrg\Profile as StudentOrgProfile;
use App\Livewire\StudentOrg\Reschedule;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\StudentOrg\TicketDetails;
use App\Livewire\Superadmin\Calendar\Index;
use App\Livewire\Superadmin\Dashboard;
// Other Imports
use App\Livewire\Superadmin\FaqEditor;
use App\Livewire\Superadmin\FaqManager;
use App\Livewire\Superadmin\Logs;
use App\Livewire\Superadmin\Permissions\Index as PermissionsIndex;
use App\Livewire\Superadmin\Profile as SuperadminProfile;
use App\Livewire\Superadmin\Roles\Index as RolesIndex;
use App\Livewire\Superadmin\Roles\Permissions as RolePermissions;
use App\Livewire\Superadmin\SystemSettings\ContentSectionEditor;
use App\Livewire\Superadmin\SystemSettings\EventTypeEditor;
use App\Livewire\Superadmin\SystemSettings\Index as SystemSettingsIndex;
use App\Livewire\Superadmin\SystemSettings\VenueEditor;
use App\Livewire\Superadmin\Tickets\TicketEditor;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use App\Livewire\Viewer\Calendar as ViewerCalendar;
use App\Livewire\Viewer\Notifications as ViewerNotifications;
use App\Livewire\Viewer\Profile as ViewerProfile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect to login page, or to user's dashboard if already authenticated
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return redirect()->route($user->getDashboardRoute());
    }

    // Redirect to login page
    return redirect()->route('login');
});

// ping route
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

// About Us - Public route
Route::get('/about-us', AboutUs::class)->name('about-us');

// FAQ - Public route
Route::get('/faq', Faq::class)->name('faq');

// Data Privacy Notice - Public route
Route::get('/data-privacy', DataPrivacyNotice::class)->name('data-privacy');

// routes/web.php — remove after confirming, or gate behind an admin check
Route::get('/debug/schedule-list', function () {
    Artisan::call('schedule:list');

    return response('<pre>'.Artisan::output().'</pre>');
})->middleware(['auth', 'role:superadmin']);

// Profile route (accessible by all authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Generic profile route - redirects based on user role
    Route::get('/profile', function () {
        return redirect()->route(match (Auth::user()->portal()) {
            'superadmin' => 'superadmin.profile',
            'osa' => 'admin.profile',
            'gso' => 'gso.profile',
            'student-org' => 'student-org.profile',
            default => 'portal.profile',
        });
    })->name('profile');

    // Shown when a user's role has no permitted pages in its portal
    Route::view('/no-access', 'no-access')->name('no-access');

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

    // Temporary upload routes (FilePond)
    Route::post('/upload-temp', [TemporaryUploadController::class, 'store'])->name('upload.temp');
    Route::delete('/upload-temp', [TemporaryUploadController::class, 'destroy'])->name('upload.temp.delete');
    Route::get('/upload-temp/restore/{id?}', [TemporaryUploadController::class, 'restore'])->name('upload.temp.restore');

    // Signed URL routes for attachment access (private storage)
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])
        ->name('attachments.preview')
        ->middleware('signed');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download')
        ->middleware('signed');

    Route::get('/attachments/draft/{token}', [DraftAttachmentController::class, 'show'])
        ->name('attachments.draft')
        ->middleware('signed');

    // Email change verification routes (signed URLs)
    Route::get('/email/verify-new/{id}/{hash}', [VerifyNewEmailController::class, 'verify'])
        ->name('email.verify-new')
        ->middleware('signed');
    Route::get('/email/cancel-change/{id}/{hash}', [VerifyNewEmailController::class, 'cancel'])
        ->name('email.cancel-change')
        ->middleware('signed');
});

// OSA routes
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'role:osa'])
    ->group(function () {
        Route::get('/dashboard', OsaDashboard::class)->name('admin.dashboard')->middleware('can:dashboard.view');
        Route::get('/tickets', TicketManagement::class)->name('admin.ticket')->middleware('can:tickets.view');
        Route::get('/ticket-review', TicketReviewIndex::class)->name('osa.ticket-review.index')->middleware('can:tickets.view');
        Route::get('/ticket-review/{ticketNumber}', TicketReviewShow::class)->name('osa.ticket-review.show')->middleware('can:tickets.view');
        Route::get('/calendar', EventCalendar::class)->name('admin.calendar')->middleware('can:calendar.view');
        Route::get('/notifications', OsaNotifications::class)->name('admin.notifications')->middleware('can:notifications.view');
        Route::get('/reports', Reports::class)->name('admin.reports')->middleware('can:reports.view');
        Route::get('/archive', Archive::class)->name('admin.archive')->middleware('can:archive.view');
        Route::get('/profile', OsaProfile::class)->name('admin.profile');
    });

// SuperAdmin routes
Route::prefix('superadmin')
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('superadmin.dashboard');
        Route::get('/users', UsersIndex::class)->name('superadmin.users');
        Route::get('/roles', RolesIndex::class)->name('superadmin.roles');
        Route::get('/roles/{roleId}/permissions', RolePermissions::class)->name('superadmin.roles.permissions');
        Route::get('/permissions', PermissionsIndex::class)->name('superadmin.permissions');
        Route::get('/calendar', Index::class)->name('superadmin.calendar');
        Route::get('/tickets', App\Livewire\Superadmin\Tickets\Index::class)->name('superadmin.tickets');
        Route::get('/tickets/create', TicketEditor::class)->name('superadmin.ticket.create');
        Route::get('/tickets/{id}/edit', TicketEditor::class)->name('superadmin.ticket.edit');
        Route::get('/archive', App\Livewire\Superadmin\Archive\Index::class)->name('superadmin.archive');
        Route::get('/reports', App\Livewire\Superadmin\Reports\Index::class)->name('superadmin.reports');
        Route::get('/notifications', App\Livewire\Superadmin\Notifications::class)->name('superadmin.notifications');
        Route::get('/system-settings', SystemSettingsIndex::class)->name('superadmin.system-settings');
        Route::get('/system-settings/content/create', ContentSectionEditor::class)->name('superadmin.content-section.create');
        Route::get('/system-settings/content/{id}/edit', ContentSectionEditor::class)->name('superadmin.content-section.edit');
        Route::get('/system-settings/event-types/create', EventTypeEditor::class)->name('superadmin.event-type.create');
        Route::get('/system-settings/event-types/{id}/edit', EventTypeEditor::class)->name('superadmin.event-type.edit');
        Route::get('/system-settings/venues/create', VenueEditor::class)->name('superadmin.venue.create');
        Route::get('/system-settings/venues/{id}/edit', VenueEditor::class)->name('superadmin.venue.edit');
        Route::get('/faqs', FaqManager::class)->name('superadmin.faqs');
        Route::get('/faqs/create', FaqEditor::class)->name('superadmin.faq.create');
        Route::get('/faqs/{id}/edit', FaqEditor::class)->name('superadmin.faq.edit');
        Route::get('/admin-tools', App\Livewire\Superadmin\AdminTools\Index::class)->name('superadmin.admin-tools');
        Route::get('/logs', Logs::class)->name('superadmin.logs');
        Route::get('/profile', SuperadminProfile::class)->name('superadmin.profile');
    });

// GSO/Offices routes
Route::prefix('gso')
    ->middleware(['auth', 'verified', 'role:gso'])
    ->group(function () {
        Route::get('/dashboard', GsoDashboard::class)->name('gso.dashboard')->middleware('can:dashboard.view');
        Route::get('/ticket-review', GsoTicketReview::class)->name('gso.ticket-review')->middleware('can:tickets.view');
        Route::get('/tickets/{ticketNumber}', GsoDetails::class)
            ->name('gso.ticket-details')
            ->middleware('can:tickets.view');
        Route::get('/calendar', GsoCalendar::class)->name('gso.calendar')->middleware('can:calendar.view');
        Route::get('/notifications', GsoNotifications::class)->name('gso.notifications')->middleware('can:notifications.view');
        Route::view('/communication', 'gso.communication')->name('gso.communication')->middleware('can:tickets.view');
        Route::get('/reports', GsoReports::class)->name('gso.reports')->middleware('can:reports.view');
        Route::get('/reports/export', [ReportsExportController::class, 'export'])
            ->name('gso.reports.export')
            ->middleware(['signed', 'can:reports.export']);
        Route::get('/profile', GsoProfile::class)->name('gso.profile');
    });

// Student Organization routes
Route::prefix('student-org')
    ->middleware(['auth', 'verified', 'role:student-org'])
    ->group(function () {
        Route::get('/dashboard', StudentOrgDashboard::class)->name('student-org.dashboard')->middleware('can:dashboard.view');
        Route::get('/submit-ticket', SubmitTicket::class)->name('student-org.submit-ticket')->middleware('can:tickets.create');
        Route::get('/my-tickets', MyTicket::class)->name('student-org.my-tickets')->middleware('can:tickets.view');
        Route::get('/tickets/{ticketNumber}', TicketDetails::class)->name('student-org.ticket-details')->middleware('can:tickets.view');
        Route::get('/calendar', Calendar::class)->name('student-org.calendar')->middleware('can:calendar.view');
        Route::get('/reschedule', Reschedule::class)->name('student-org.reschedule')->middleware('can:tickets.reschedule');
        Route::get('/notifications', Notifications::class)->name('student-org.notifications')->middleware('can:notifications.view');
        Route::get('/history', History::class)->name('student-org.history')->middleware('can:tickets.view');
        Route::get('/profile', StudentOrgProfile::class)->name('student-org.profile');
    });

// Viewer portal (custom read-only roles, e.g. security staff)
Route::prefix('portal')
    ->middleware(['auth', 'verified', 'role:viewer'])
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route(Auth::user()->getDashboardRoute());
        })->name('portal.dashboard');
        Route::get('/calendar', ViewerCalendar::class)->name('portal.calendar')->middleware('can:calendar.view');
        Route::get('/notifications', ViewerNotifications::class)->name('portal.notifications')->middleware('can:notifications.view');
        Route::get('/profile', ViewerProfile::class)->name('portal.profile');
    });

require __DIR__.'/auth.php';
