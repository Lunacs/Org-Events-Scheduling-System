<div style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, Noto Sans, 'Apple Color Emoji', 'Segoe UI Emoji'; max-width: 560px; margin: 0 auto; padding: 24px;">
	<h1 style="font-size: 18px; margin: 0 0 12px; color: #059669;">GSO Approved Ticket</h1>
	<p style="margin: 0 0 8px; color: #374151;">
		The General Services Office (GSO) has approved the ticket: <strong>{{ $ticket->title }}</strong>
	</p>
	<p style="margin: 0 0 8px; color: #6b7280;">
		This ticket is now awaiting your final approval decision.
	</p>
	@if(!empty($remarks))
		<div style="margin: 12px 0; padding: 12px; background: #d1fae5; color: #065f46; border-left: 4px solid #059669; border-radius: 6px;">
			<p style="margin: 0 0 4px; font-weight: 600;">GSO Remarks:</p>
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
	<p style="margin: 0 0 20px;">
		<a href="{{ $actionUrl }}" style="display: inline-block; background: #059669; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px;">
			{{ $actionText ?? 'Review for Final Approval' }}
		</a>
	</p>
	<p style="margin: 24px 0 0; color: #9ca3af; font-size: 12px;">This is an automated message. Please do not reply.</p>
</div>

