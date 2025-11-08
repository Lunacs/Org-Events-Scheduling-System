<div style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, 'Apple Color Emoji', 'Segoe UI Emoji'; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 18px; margin: 0 0 12px;">{{ $title ?? 'Ticket Status Updated' }}</h1>
	<p style="margin: 0 0 8px; color: #374151;">{{ $statusMessage }}</p>
	@if(!empty($remarks))
		<p style="margin: 0 0 8px; color: #374151;"><strong>Remarks:</strong> {{ $remarks }}</p>
	@endif
	<p style="margin: 0 0 16px; color: #6b7280;">
		Ticket Number: <strong>{{ $ticket->ticket_number }}</strong>
	</p>
	<p style="margin: 0 0 20px;">
		<a href="{{ $actionUrl }}" style="display: inline-block; background: #111827; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px;">
			{{ $actionText ?? 'View Ticket' }}
		</a>
	</p>
	<p style="margin: 24px 0 0; color: #9ca3af; font-size: 12px;">This is an automated message. Please do not reply.</p>
</div>


