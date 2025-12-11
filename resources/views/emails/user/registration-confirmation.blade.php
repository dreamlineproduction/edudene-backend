<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations on your registration</title>
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
        .content {
           
        }
        .content .button-outer{
            margin: 25px 0px
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
            line-height: 1.4;
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
            <h1>Congratulations!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>
            <p>Congratulations! Your registration has been successfully completed.</p>
            <p>You can now log in using the credentials provided during registration and access your dashboard for further updates, class details, and important announcements.</p>

            <div class="button-outer">
                <a href="{{ $mailData['loginLink'] }}" class="button">
                    Login
                </a>
            </div>

            <p>If the button doesn’t work, you can also copy and paste this link into your browser:</p>
            <p>
                <a href="{{ $mailData['loginLink'] }}">{{ $mailData['loginLink'] }}</a>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                If you have any questions or need assistance, feel free to contact us anytime. 
                <a href="mailto:{{ env('SUPPORT_EMAIL') }}">{{ env('SUPPORT_EMAIL') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>