{{-- filepath: d:\Coding\laragon\www\org-events-scheduling-system\resources\views\emails\verify-email.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .email-wrapper {
            background-color: #f3f4f6;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .header p {
            color: #d1fae5;
            margin: 8px 0 0 0;
            font-size: 14px;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1f2937;
        }

        .message {
            margin-bottom: 25px;
            color: #4b5563;
            line-height: 1.8;
            font-size: 15px;
        }

        .button-container {
            text-align: center;
            margin: 35px 0;
        }

        .verify-button {
            display: inline-block;
            padding: 16px 48px;
            background-color: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .verify-button:hover {
            background-color: #059669;
        }

        .notice-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 25px 0;
            border-radius: 6px;
        }

        .notice-box p {
            margin: 0;
            color: #78350f;
            font-size: 14px;
        }

        .alternative-link {
            margin-top: 25px;
            padding: 16px;
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .alternative-link p {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #6b7280;
        }

        .alternative-link a {
            color: #10b981;
            word-break: break-all;
            font-size: 12px;
        }

        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <h1>Email Verification</h1>
                <p>PLV Event Scheduling System</p>
            </div>

            <div class="content">
                <div class="greeting">Dear {{ $user->name }},</div>

                <div class="message">
                    <p>Please verify your email address to activate your account in the PLV Event Scheduling System.</p>
                </div>

                <div class="button-container">
                    <a href="{{ $verificationUrl }}" class="verify-button">Verify Email Address</a>
                </div>

                <div class="notice-box">
                    <p><strong>Note:</strong> This verification link will expire in 60 minutes.</p>
                </div>

                <div class="message">
                    <p style="color: #6b7280; font-size: 14px;">If you did not create this account, please disregard
                        this email.</p>
                </div>

                <div class="alternative-link">
                    <p>If the button doesn't work, copy this link:</p>
                    <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
                </div>
            </div>

            <div class="footer">
                <p><strong>Office of Student Affairs</strong></p>
                <p style="margin-top: 10px; font-size: 12px; color: #9ca3af;">
                    © {{ date('Y') }} Pamantasan ng Lungsod ng Valenzuela
                </p>
            </div>
        </div>
    </div>
</body>

</html>
