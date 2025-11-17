<div style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, 'Apple Color Emoji', 'Segoe UI Emoji'; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 18px; margin: 0 0 12px; color: #dc2626;">GSO Rejected Ticket</h1>
	<p style="margin: 0 0 8px; color: #374151;">
		The General Services Office (GSO) has rejected the ticket: <strong>{{ $ticket->title }}</strong>
	</p>
	<p style="margin: 0 0 8px; color: #6b7280;">
		This ticket requires your final decision. You may agree with GSO's rejection or override it if appropriate.
	</p>
	@if(!empty($remarks))
		<div style="margin: 12px 0; padding: 12px; background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; border-radius: 6px;">
			<p style="margin: 0 0 4px; font-weight: 600;">GSO Rejection Reason:</p>
			<p style="margin: 0;">{{ $remarks }}</p>
		</div>
	@endif
	<p style="margin: 0 0 16px; color: #6b7280;">Ticket Number: <strong>{{ $ticket->ticket_number }}</strong></p>
	<div style="margin: 12px 0; padding: 10px 12px; background: #f3f4f6; border-radius: 6px;">
		<p style="margin: 0; color: #374151; font-size: 14px;">
			<strong>Event Details:</strong><br>
			Date Requested: {{ $ticket->{'date-requested'} }}<br>
			Venue Requested: {{ $ticket->{'venue-requested'} }}
		</p>
	</div>
	<p style="margin: 0 0 8px; color: #374151; font-size: 14px;">
		<strong>Next Steps:</strong>
	</p>
	<ul style="margin: 0 0 16px; padding-left: 20px; color: #6b7280;">
		<li>Review GSO's rejection reason carefully</li>
		<li>Consider if the rejection is justified</li>
		<li>Make your final decision (approve or reject)</li>
	</ul>
	<p style="margin: 0 0 20px;">
		<a href="{{ $actionUrl }}" style="display: inline-block; background: #dc2626; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px;">
			{{ $actionText ?? 'Review Ticket' }}
		</a>
	</p>
	<p style="margin: 24px 0 0; color: #9ca3af; font-size: 12px;">This is an automated message. Please do not reply.</p>
</div>

