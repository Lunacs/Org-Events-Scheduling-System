<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Ticket Forwarded to GSO</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f9fafb; margin: 0; padding: 0;">
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; padding: 40px 20px;">
		<tr>
			<td align="center">
				<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
					<!-- Header -->
					<tr>
						<td style="background-color: #ffffff; padding: 32px 30px 24px; border-bottom: 1px solid #e5e7eb;">
							<h1 style="color: #111827; margin: 0; font-size: 22px; font-weight: 600;">Ticket Forwarded to GSO</h1>
							<p style="color: #6b7280; margin: 6px 0 0 0; font-size: 14px;">PLV Event Scheduling System</p>
						</td>
					</tr>

					<!-- Content -->
					<tr>
						<td style="padding: 30px;">
							<!-- Greeting -->
							<p style="font-size: 16px; font-weight: 600; margin: 0 0 20px 0; color: #1f2937;">
								Good day!
							</p>

							<!-- Message -->
							<p style="margin: 0 0 25px 0; color: #374151; font-size: 15px; line-height: 1.8;">
								A ticket has been forwarded to the General Services Office (GSO) for facility and resource review.
							</p>

							<!-- Info Box -->
							<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
								<tr>
									<td style="background-color: #eff6ff; border-left: 3px solid #3b82f6; padding: 16px; border-radius: 0 6px 6px 0;">
										<p style="margin: 0; color: #1e40af; font-size: 14px;"><strong>→ Forwarded:</strong> This ticket is now pending GSO review for venue and resources.</p>
									</td>
								</tr>
							</table>

							<!-- Ticket Details Box -->
							<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 25px;">
								<tr>
									<td style="padding: 20px;">
										<h3 style="margin: 0 0 12px 0; font-size: 14px; color: #374151; font-weight: 600;">Ticket Details</h3>
										<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td style="color: #6b7280; font-size: 13px; padding-bottom: 6px;">
													<strong style="color: #374151;">Title:</strong> {{ $ticket->title }}
												</td>
											</tr>
											<tr>
												<td style="color: #6b7280; font-size: 13px; padding-bottom: 6px;">
													<strong style="color: #374151;">Ticket Number:</strong> {{ $ticket->ticket_number }}
												</td>
											</tr>
											@if($ticket->{'date-requested'})
											<tr>
												<td style="color: #6b7280; font-size: 13px; padding-bottom: 6px;">
													<strong style="color: #374151;">Date Requested:</strong> {{ $ticket->{'date-requested'} }}
												</td>
											</tr>
											@endif
											@if($ticket->{'venue-requested'})
											<tr>
												<td style="color: #6b7280; font-size: 13px;">
													<strong style="color: #374151;">Venue:</strong> {{ $ticket->{'venue-requested'} }}
												</td>
											</tr>
											@endif
										</table>
									</td>
								</tr>
							</table>

							@if(!empty($remarks))
							<!-- Remarks Box -->
							<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
								<tr>
									<td style="background-color: #f9fafb; border-left: 3px solid #6b7280; padding: 16px; border-radius: 0 6px 6px 0;">
										<p style="margin: 0 0 8px 0; color: #374151; font-size: 14px; font-weight: 600;">Remarks:</p>
										<p style="margin: 0; color: #4b5563; font-size: 14px;">{{ $remarks }}</p>
									</td>
								</tr>
							</table>
							@endif

							<!-- Action Button -->
							<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 30px 0;">
								<tr>
									<td align="center">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0">
											<tr>
												<td style="border-radius: 8px; background-color: #111827;">
													<a href="{{ $actionUrl }}" style="display: inline-block; padding: 14px 36px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 15px;">{{ $actionText ?? 'Review Tickets' }}</a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>

					<!-- Footer -->
					<tr>
						<td style="background-color: #f9fafb; padding: 24px 30px; border-top: 1px solid #e5e7eb; text-align: center;">
							<p style="margin: 0; color: #374151; font-size: 13px; font-weight: 600;">Office of Student Affairs</p>
							<p style="margin: 8px 0 0 0; font-size: 12px; color: #9ca3af;">
								© {{ date('Y') }} Pamantasan ng Lungsod ng Valenzuela
							</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>

</html>