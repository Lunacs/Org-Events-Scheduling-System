<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Email Preview Routes (Development Only)
|--------------------------------------------------------------------------
|
| These routes allow you to preview email templates in the browser.
| Remove or disable these in production!
|
*/

Route::prefix('email-preview')->middleware(['web'])->group(function () {

    // Create mock data for previews
    $getMockTicket = function () {
        return (object) [
            'id' => 1,
            'title' => 'Annual Leadership Training Workshop',
            'ticket_number' => 'TKT-2025-0001',
            'date-requested' => 'January 15, 2025',
            'venue-requested' => 'PLV Auditorium',
            'status' => 'pending',
        ];
    };

    $getMockUser = function () {
        return (object) [
            'id' => 1,
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@plv.edu.ph',
            'role_display' => 'Student Organization',
        ];
    };

    $getMockComment = function () {
        return (object) [
            'id' => 1,
            'content' => 'This is a sample comment on the ticket. Please review the attached documents.',
        ];
    };

    // Index - List all email templates
    Route::get('/', function () {
        $templates = [
            'Auth Emails' => [
                ['name' => 'Password Reset', 'url' => '/email-preview/reset-password'],
                ['name' => 'Verify Email', 'url' => '/email-preview/verify-email'],
                ['name' => 'Password Changed', 'url' => '/email-preview/password-changed'],
            ],
            'Ticket Emails' => [
                ['name' => 'Ticket Submitted', 'url' => '/email-preview/ticket-submitted'],
                ['name' => 'Ticket Approved', 'url' => '/email-preview/ticket-approved'],
                ['name' => 'Ticket For Revision', 'url' => '/email-preview/ticket-for-revision'],
                ['name' => 'Ticket Amended', 'url' => '/email-preview/ticket-amended'],
                ['name' => 'Ticket Comment', 'url' => '/email-preview/ticket-comment'],
                ['name' => 'Ticket Forwarded to GSO', 'url' => '/email-preview/ticket-forwarded-gso'],
                ['name' => 'Ticket Revision Requested', 'url' => '/email-preview/ticket-revision-requested'],
                ['name' => 'Ticket Status Updated', 'url' => '/email-preview/ticket-status-updated'],
                ['name' => 'GSO Approved', 'url' => '/email-preview/gso-approved'],
                ['name' => 'GSO For Revision', 'url' => '/email-preview/gso-for-revision'],
            ],
        ];

        $html = '<html><head><title>Email Template Preview</title>';
        $html .= '<style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; background: #f3f4f6; }
            h1 { color: #111827; margin-bottom: 30px; }
            h2 { color: #374151; margin-top: 30px; margin-bottom: 15px; font-size: 18px; }
            .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            a { color: #2563eb; text-decoration: none; display: block; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
            a:hover { color: #1d4ed8; }
            a:last-child { border-bottom: none; }
            .warning { background: #fef3c7; border: 1px solid #fde68a; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #92400e; }
        </style></head><body>';
        $html .= '<h1>📧 Email Template Preview</h1>';
        $html .= '<div class="warning">⚠️ <strong>Development Only:</strong> Remove these routes before deploying to production.</div>';

        foreach ($templates as $category => $items) {
            $html .= "<h2>{$category}</h2>";
            $html .= '<div class="card">';
            foreach ($items as $item) {
                $html .= "<a href=\"{$item['url']}\" target=\"_blank\">{$item['name']} →</a>";
            }
            $html .= '</div>';
        }

        $html .= '</body></html>';

        return $html;
    });

    // Auth Emails
    Route::get('/reset-password', function () use ($getMockUser) {
        return view('emails.reset-password', [
            'user' => $getMockUser(),
            'resetUrl' => url('/reset-password/sample-token'),
        ]);
    });

    Route::get('/verify-email', function () use ($getMockUser) {
        return view('emails.verify-email', [
            'user' => $getMockUser(),
            'verificationUrl' => url('/verify-email/sample-token'),
        ]);
    });

    Route::get('/password-changed', function () use ($getMockUser) {
        return view('emails.password-changed', [
            'user' => $getMockUser(),
            'changedAt' => now()->format('F j, Y g:i A'),
            'ipAddress' => '192.168.1.100',
        ]);
    });

    // Ticket Emails
    Route::get('/ticket-submitted', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-submitted', [
            'ticket' => $getMockTicket(),
            'actionUrl' => url('/tickets'),
            'actionText' => 'View Tickets',
        ]);
    });

    Route::get('/ticket-approved', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-approved', [
            'ticket' => $getMockTicket(),
            'actionUrl' => url('/tickets/1'),
            'actionText' => 'View Ticket',
        ]);
    });

    Route::get('/ticket-for-revision', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-for-revision', [
            'ticket' => $getMockTicket(),
            'remarks' => 'Please update the event date as it conflicts with another scheduled activity. Also, provide more details about the expected number of attendees.',
            'actionUrl' => url('/tickets/1'),
            'actionText' => 'View Ticket',
        ]);
    });

    Route::get('/ticket-amended', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-amended', [
            'ticket' => $getMockTicket(),
            'remarks' => 'Updated the event date and venue as requested.',
            'actionUrl' => url('/tickets/1'),
            'actionText' => 'Review Ticket',
        ]);
    });

    Route::get('/ticket-comment', function () use ($getMockTicket, $getMockUser, $getMockComment) {
        return view('emails.tickets.ticket-comment', [
            'ticket' => $getMockTicket(),
            'commenter' => $getMockUser(),
            'comment' => $getMockComment(),
            'greetingName' => 'Maria Santos',
            'actionUrl' => url('/tickets/1'),
            'actionText' => 'View Ticket',
        ]);
    });

    Route::get('/ticket-forwarded-gso', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-forwarded-gso', [
            'ticket' => $getMockTicket(),
            'remarks' => 'Please verify venue availability and required equipment.',
            'actionUrl' => url('/gso/tickets'),
            'actionText' => 'Review Tickets',
        ]);
    });

    Route::get('/ticket-revision-requested', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-revision-requested', [
            'ticket' => $getMockTicket(),
            'remarks' => 'The proposed budget breakdown needs more detail. Please itemize the expenses for catering and materials.',
            'actionUrl' => url('/tickets/1/edit'),
            'actionText' => 'View and Revise Ticket',
        ]);
    });

    Route::get('/ticket-status-updated', function () use ($getMockTicket) {
        return view('emails.tickets.ticket-status-updated', [
            'ticket' => $getMockTicket(),
            'title' => 'Ticket Status Updated',
            'statusMessage' => 'Your ticket status has been updated. Please see the details below.',
            'oldStatus' => 'pending',
            'newStatus' => 'approved',
            'remarks' => 'Approved for implementation. Good luck with your event!',
            'actionUrl' => url('/tickets/1'),
            'actionText' => 'View Ticket',
        ]);
    });

    Route::get('/gso-approved', function () use ($getMockTicket) {
        return view('emails.tickets.gso-approved', [
            'ticket' => $getMockTicket(),
            'remarks' => 'Venue and equipment confirmed. Audio system will be set up by 8 AM.',
            'actionUrl' => url('/osa/tickets/1'),
            'actionText' => 'Review for Final Approval',
        ]);
    });

    Route::get('/gso-for-revision', function () use ($getMockTicket) {
        return view('emails.tickets.gso-for-revision', [
            'ticket' => $getMockTicket(),
            'remarks' => 'The requested venue is not available on the specified date. Please select an alternative venue or date.',
            'actionUrl' => url('/osa/tickets/1'),
            'actionText' => 'Review Ticket',
        ]);
    });
});
