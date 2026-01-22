<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Inactive From - {{ env('WEBSITE_NAME') }}</title>
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
        <h1>Course Inactive</h1>
    </div>

    <!-- Content -->
    <div class="content">
        <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>

        <p>
            We would like to inform you that the course you created on 
            <strong>{{ env('WEBSITE_NAME') }}</strong> has been reviewed by our administration team
            and is currently marked as <strong>Inactive / Blocked</strong>.
        </p>

        <div class="warning-box">
            <p><strong>Reason for Inactivation:</strong></p>
            <p>
                {{ $mailData['reason'] ?? 'The course does not currently comply with our platform guidelines or quality standards.' }}
            </p>
        </div>

        <p>
            During this period, your course will not be visible to users on the platform.
            You are advised to review the feedback mentioned above and update the course accordingly.
        </p>

        <p>
            Once the required changes have been made, you may resubmit the course for review.
            Our team will re-evaluate it as soon as possible.
        </p>

        <p>
            If you feel this action was taken in error or need further assistance,
            please contact our support team for clarification.
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
