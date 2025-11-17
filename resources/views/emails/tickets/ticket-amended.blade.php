<div style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, 'Apple Color Emoji', 'Segoe UI Emoji'; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 18px; margin: 0 0 12px; color: #2563eb;">Ticket Amended and Resubmitted</h1>
	<p style="margin: 0 0 8px; color: #374151;">
		A ticket has been amended and resubmitted for your review: <strong>{{ $ticket->title }}</strong>
	</p>
	<p style="margin: 0 0 8px; color: #6b7280;">
		The student organization has made changes to their ticket based on your previous feedback.
	</p>
	@if(!empty($remarks))
		<div style="margin: 12px 0; padding: 12px; background: #dbeafe; color: #1e3a8a; border-left: 4px solid #2563eb; border-radius: 6px;">
			<p style="margin: 0 0 4px; font-weight: 600;">Submitter's Note:</p>
			<p style="margin: 0;">{{ $remarks }}</p>
		</div>
	@endif
	<p style="margin: 0 0 16px; color: #6b7280;">Ticket Number: <strong>{{ $ticket->ticket_number }}</strong></p>
	<p style="margin: 0 0 20px;">
		<a href="{{ $actionUrl }}" style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px;">
			{{ $actionText ?? 'Review Ticket' }}
		</a>
	</p>
	<p style="margin: 24px 0 0; color: #9ca3af; font-size: 12px;">This is an automated message. Please do not reply.</p>
</div>

