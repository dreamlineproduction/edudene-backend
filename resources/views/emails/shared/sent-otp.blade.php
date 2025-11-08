<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 20px 0;
            padding-bottom: 0px;
            text-align: center;
        }
        .header img {
            
            max-width: 250px;
        }
        .header h1{
            font-size: 24px;
            margin-top: 40px;
            text-align: left;
        }
       
        p strong{
            font-weight: 600;
            color: #777;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
        }
        p{
            word-wrap: break-word;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            {{-- <img src="{{ asset('images/edudene_purple.svg') }}" alt="{{ config('app.name') }} Logo"> --}}
            <img src="https://edudene.com/public/assets/img/logos/edudene_purple.svg" alt="{{ config('app.name') }} Logo">
            
            <h1>Security Verification</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>
            <p>We noticed that you initiated a sensitive account action on 
                <strong>{{ config('app.name') }}</strong> — such as updating your password, 
                enabling two-factor authentication, or changing your profile details.
            </p>
            <p>To confirm that this action was initiated by you, please verify it using the One-Time Password (OTP) below:</p>

            <h2>
                {{ $mailData['otp'] ?? 'XXXXXX' }}
            </h2>
            <p>This OTP will expire in <strong>10 minutes</strong>.</p>            
            <p>If you did not request this change, please <strong>do not share this code</strong> and contact our support team immediately.</p>

            <br>
            <p>Thank you</p>
            <p>
                <a href="{{ env('WEBSITE_URL') }}" title="{{ env('WEBSITE_URL') }}" style="color: #6c2bd9; text-decoration: none;">
                    {{ env('WEBSITE_NAME') }} 
                    Team
                </a> 
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>Need help? Contact us at 
                <a href="mailto:{{ env('SUPPORT_EMAIL') }}" style="color: #6c2bd9;">
                    {{ env('SUPPORT_EMAIL') }}
                </a>
            </p>
        </div>
    </div>
</body>
</html>