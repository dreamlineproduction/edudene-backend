<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deactive - {{ env('WEBSITE_NAME') }}</title>
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
        .header h1 {
            font-size: 24px;
            margin-top: 40px;
            text-align: left;
            color: #d9534f;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
        }
        p {
            word-wrap: break-word;
            line-height: 1.6;
        }
        .warning-box {
            background-color: #ffdddd;
            border-left: 5px solid #d34d4d;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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
            <img src="https://edudene.com/public/assets/img/logos/edudene_purple.svg" alt="{{ config('app.name') }} Logo">
            <h1>Account Inactive</h1>
        </div>
        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>
            <p>
                @if(!empty($mailData['schoolName']))                
                    This email is to inform you that your account on 
                    <strong>{{ $mailData['schoolName'] }}</strong> has been <strong>temporarily inactive</strong> by the administrator.
                @else
                    This email is to inform you that your account on 
                    <strong>{{ env('WEBSITE_NAME') }}</strong> has been <strong>temporarily inactive</strong> by the administrator.
                @endif
            </p>

            <div class="warning-box">
                <p><strong>Reason for Account Inactivation:</strong></p>
                <p>
                    {{ $mailData['description'] 
                    ?? 'Your account has been found in violation of our platform policies or pending administrative review.' 
                    }}
                </p>
            </div>
            <p>
                Until this issue is resolved, you will not be able to access or use the platform services.
                We request you to review the reason carefully and take the necessary corrective actions.
            </p>
            <p>
                If you believe this action was taken in error or need further clarification, please contact our
                support team. Our team will be happy to assist you.
            </p>
            <p>Regards,</p>
            <p>
                <a href="{{ env('WEBSITE_URL') }}" title="{{ env('WEBSITE_URL') }}">
                {{ env('WEBSITE_NAME') }} Administration Team
                </a>
            </p>
        </div>
        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                Need help? Contact us at 
                <a href="mailto:{{ env('SUPPORT_EMAIL') }}">{{ env('SUPPORT_EMAIL') }}</a>
            </p>
        </div>
    </div>
</body>
</html>