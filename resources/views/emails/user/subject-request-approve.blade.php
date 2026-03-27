<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Request Decline From - {{ env('WEBSITE_NAME') }}</title>
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
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .approved-box {
            background-color: #e6fffa;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #065f46;
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
        <h1>Course Decline</h1>
    </div>

    <!-- Content -->
    <div class="content">
        <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>

        <p>
            We are pleased to inform you that your request to add a new subject on 
            <strong>{{ env('WEBSITE_NAME') }}</strong> has been reviewed and approved by our administration team.
        </p>

        <div class="approved-box">
            <p><strong>Request Status:</strong> Approved</p>
            <p>
                The subject you requested has been successfully approved and is now available on the platform.
                You may proceed with adding related courses or content under this subject.
            </p>
        </div>

        <p>
            Thank you for contributing to our platform and helping us expand the range of learning opportunities.
        </p>

        <p>
            If you have any questions or need further assistance, please feel free to contact our support team.
        </p>

        <p>Regards,</p>

        <p>
            <a href="{{ env('FRONTEND_URL') }}" title="{{ env('FRONTEND_URL') }}">
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
