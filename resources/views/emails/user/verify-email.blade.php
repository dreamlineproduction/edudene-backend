<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
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
            text-align: center;
            padding: 20px 0;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0056b3;
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
            <img src="{{ asset('images/edudene_purple.svg') }}" alt="{{ config('app.name') }} Logo">
            <h1>Welcome to {{ config('app.name') }}!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['mail'] ?? 'User' }},</p>
            <p>Thank you for registering with {{ config('app.name') }}! Please verify your email by clicking the button below:</p>
            <p>
                <a href="{{ $mailData['activationLink'] }}" class="button">Verify Your Email</a>
            </p>
            <p>Or copy and paste the following link into your browser:</p>
            <p>
                <a href="{{ $mailData['activationLink'] }}">{{ $mailData['activationLink'] }}</a>
            </p>
            <p>If you did not create an account, please ignore this email.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ env('app.name') }}. All rights reserved.</p>
            <p>If you have any questions, contact us at <a href="mailto:{{ env('SUPPORT_EMAIL') }}">{{ env('SUPPORT_EMAIL') }}</a>.</p>
        </div>
    </div>
</body>
</html>