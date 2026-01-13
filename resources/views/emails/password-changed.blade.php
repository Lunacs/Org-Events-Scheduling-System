<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f9fafb; margin: 0; padding: 0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 32px 30px 24px; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="color: #111827; margin: 0; font-size: 22px; font-weight: 600;">Password Changed Successfully</h1>
                            <p style="color: #6b7280; margin: 6px 0 0 0; font-size: 14px;">PLV Event Scheduling System</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <!-- Greeting -->
                            <p style="font-size: 16px; font-weight: 600; margin: 0 0 20px 0; color: #1f2937;">
                                Dear {{ $user->name ?? 'User' }},
                            </p>

                            <!-- Message -->
                            <p style="margin: 0 0 25px 0; color: #374151; font-size: 15px; line-height: 1.8;">
                                This email confirms that your password has been successfully changed for your PLV Event Scheduling System account.
                            </p>

                            <!-- Success Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="background-color: #ecfdf5; border-left: 3px solid #10b981; padding: 16px; border-radius: 0 6px 6px 0;">
                                        <p style="margin: 0; color: #065f46; font-size: 14px;"><strong>✓ Password Updated:</strong> Your new password is now active. You can use it to log in to your account.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Details Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #374151; font-weight: 600;">Change Details</h3>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="color: #6b7280; font-size: 13px; padding-bottom: 6px;">
                                                    <strong style="color: #374151;">Date & Time:</strong> {{ $changedAt }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6b7280; font-size: 13px; padding-bottom: 6px;">
                                                    <strong style="color: #374151;">Email:</strong> {{ $user->email }}
                                                </td>
                                            </tr>
                                            @if($ipAddress)
                                            <tr>
                                                <td style="color: #6b7280; font-size: 13px;">
                                                    <strong style="color: #374151;">IP Address:</strong> {{ $ipAddress }}
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #fef2f2; border-left: 3px solid #ef4444; padding: 16px; border-radius: 0 6px 6px 0;">
                                        <p style="margin: 0; color: #7f1d1d; font-size: 14px;"><strong>⚠️ Didn't make this change?</strong> If you did not reset your password, your account may be compromised. Please contact the Office of Student Affairs immediately or reply to this email for assistance.</p>
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