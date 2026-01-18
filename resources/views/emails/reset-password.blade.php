<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f9fafb; margin: 0; padding: 0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9fafb; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 32px 30px 24px; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="color: #111827; margin: 0; font-size: 22px; font-weight: 600;">Password Reset</h1>
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
                                A password reset has been requested for your account. Click the button below to proceed.
                            </p>

                            <!-- Action Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 35px 0;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="border-radius: 8px; background-color: #111827;">
                                                    <a href="{{ $resetUrl }}" style="display: inline-block; padding: 16px 48px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px;">Reset Password</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Notice Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="background-color: #fef3c7; border-left: 3px solid #f59e0b; padding: 16px; border-radius: 0 6px 6px 0;">
                                        <p style="margin: 0; color: #78350f; font-size: 14px;"><strong>Note:</strong> This link will expire in 60 minutes.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="background-color: #fef2f2; border-left: 3px solid #ef4444; padding: 16px; border-radius: 0 6px 6px 0;">
                                        <p style="margin: 0; color: #7f1d1d; font-size: 14px;"><strong>Security:</strong> If you did not request this reset, please ignore this email. Your password will remain unchanged.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternative Link -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #f9fafb; padding: 16px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280;">If the button doesn't work, copy this link:</p>
                                        <a href="{{ $resetUrl }}" style="color: #2563eb; word-break: break-all; font-size: 12px;">{{ $resetUrl }}</a>
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