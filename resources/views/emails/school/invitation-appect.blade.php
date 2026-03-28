<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
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
        }
        .header {
            text-align: center;
        }
        .header img {
            max-width: 220px;
        }
        .header h1 {
            margin-top: 20px;
            text-align: left;
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
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
        .credentials {
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <img src="https://edudene.com/public/assets/img/logos/edudene_purple.svg">
        <h1>Congratulations!</h1>
    </div>

    <!-- Content -->
    <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>

    <p>
        Your invitation has been successfully accepted. Welcome to 
        <strong>{{ $mailData['schoolName'] }}</strong>
    </p>

    <p>You can now log in to your account using the details below:</p>

    <!-- Login Details -->
    <div class="credentials">
        <p><strong>Email:</strong> {{ $mailData['email'] }}</p>
        <p><strong>Password:</strong> {{ $mailData['password'] ?? 'Use your existing password' }}</p>
    </div>

    <p>Click below to login and start your journey</p>

    <p>
        <a href="{{ $mailData['loginLink'] }}" class="button">
            Login Now
        </a>
    </p>

    <p>If the button doesn’t work, use this link:</p>
    <p>
        <a href="{{ $mailData['loginLink'] }}">{{ $mailData['loginLink'] }}</a>
    </p>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        <p>
            Need help? 
            <a href="mailto:{{ env('SUPPORT_EMAIL') }}">
                {{ env('SUPPORT_EMAIL') }}
            </a>
        </p>
    </div>

</div>

</body>
</html>