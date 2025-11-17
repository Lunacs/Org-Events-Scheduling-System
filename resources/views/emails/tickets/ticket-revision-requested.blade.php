<div
    style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, 'Apple Color Emoji', 'Segoe UI Emoji'; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="font-size: 18px; margin: 0 0 12px; color: #d97706;">Revision Required</h1>
    <p style="margin: 0 0 8px; color: #374151;">
        Your ticket <strong>{{ $ticket->title }}</strong> requires revision before it can be processed.
    </p>
    @if (!empty($remarks))
        <div
            style="margin: 12px 0; padding: 12px; background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; border-radius: 6px;">
            <p style="margin: 0 0 4px; font-weight: 600;">Requested Changes:</p>
            <p style="margin: 0;">{{ $remarks }}</p>
        </div>
    @endif
    <p style="margin: 0 0 16px; color: #6b7280;">Ticket Number: <strong>{{ $ticket->ticket_number }}</strong></p>
    <p style="margin: 0 0 8px; color: #374151;">
        Please review the comments and make the necessary changes to your ticket.
    </p>
    <p style="margin: 0 0 20px;">
        <a href="{{ $actionUrl }}"
            style="display: inline-block; background: #f59e0b; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px;">
            {{ $actionText ?? 'View and Revise Ticket' }}
        </a>
    </p>
    <p style="margin: 24px 0 0; color: #9ca3af; font-size: 12px;">This is an automated message. Please do not reply.</p>
</div>
