<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Approval Request</title>
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
        .header h1 {
            font-size: 24px;
            margin-top: 40px;
            text-align: left;
            color: #2c3e50;
        }
        .content {
            padding: 20px 0;
        }
        .content p {
            margin: 10px 0;
            line-height: 1.6;
        }
        .course-details {
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .course-details label {
            font-weight: 600;
            color: #555;
        }
        .course-details p {
            margin: 8px 0;
            color: #666;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Course Approval Request</h1>
        </div>

        <div class="content">
            <p>Hello Admin,</p>

            <p>A new course has been submitted for approval. Below are the details:</p>

            <div class="course-details">
                <p><label>Course Title:</label> {{ $course->title }}</p>
                <p><label>Instructor:</label> {{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}</p>
                <p><label>Instructor Email:</label> {{ $user->email ?? 'N/A' }}</p>
                <p><label>Course Level:</label> {{ $course->level ?? 'N/A' }}</p>
                <p><label>Short Description:</label> {{ $course->short_description ?? 'N/A' }}</p>
                <p><label>Submission Date:</label> {{ $course->created_at->format('M d, Y') ?? 'N/A' }}</p>
            </div>

            <p>Please review the course details and approve or decline it from the admin dashboard.</p>

            {{-- <a href="{{ config('app.url') }}/admin/courses" class="button">Review Course</a> --}}

            <p>Best regards,<br>
            The Edudene Team</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Edudene. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
