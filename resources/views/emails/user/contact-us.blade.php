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
            <h1>Contact Us</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $mailData['fullName'] ?? 'User' }},</p>
            <p>Thank you for reaching out to us. We have received your message and will get back to you as soon as possible.</p>
            <p>Best regards,</p>
            <p>
                <a href="{{ env('WEBSITE_URL') }}" title="{{ env('WEBSITE_URL') }}">
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