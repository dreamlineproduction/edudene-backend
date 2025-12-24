<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class has been approved</title>
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
            line-height: 1.5;
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
            <!-- <img src="{{ asset('images/edudene_purple.svg') }}" alt="{{ config('app.name') }} Logo"> -->
            <img src="https://edudene.com/public/assets/img/logos/edudene_purple.svg" alt="{{ config('app.name') }} Logo">
            <h1>Class Timeline</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['fullName'] ?? 'Tutor' }},</p>

            <p>
                We regret to inform you that your 
                <strong>Yous class has been declined by the {{$mailData['schoolName']}}</strong> at this time.
            </p>

            <p><strong>Reason:</strong></p>
            <p style="background:#f8f8f8;padding:10px;border-left:4px solid #dc3545;">
                {{ $mailData['reason'] ?? 'We were unable to verify your request.' }}
            </p>

            <div class="button-outer">
                <a href="{{ env('WEBSITE_URL') }}/#" class="button">
                    Review Class
                </a>
            </div>

            <p>
                If you believe this was a mistake or need clarification, please contact our support team.
            </p>

            <p>Best regards,</p>

            <p>
                <a href="{{ env('WEBSITE_URL') }}">
                    {{ env('WEBSITE_NAME') }} Team
                </a>
            </p>
        </div>


        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>If you have any questions, please contact us at 
                <a href="mailto:{{ env('SUPPORT_EMAIL') }}">{{ env('SUPPORT_EMAIL') }}</a>.
            </p>
        </div>
    </div>

</body>
</html>