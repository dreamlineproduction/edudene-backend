<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Subject Request</title>
</head>
<body>
    <h2>New Subject Request from Tutor</h2>
    <p><strong>Tutor Name:</strong> {{ $tutor->full_name }}</p>
    <p><strong>Email:</strong> {{ $tutor->email }}</p>
    <p><strong>Category:</strong> {{ $subjectRequest->category ? $subjectRequest->category->title : '' }}</p>
    <p><strong>Field:</strong> {{ $subjectRequest->subCategory ? $subjectRequest->subCategory->title : '' }}</p>
    <p><strong>Course:</strong> {{ $subjectRequest->subSubCategory ? $subjectRequest->subSubCategory->title : '' }}</p>
    <p><strong>Requested Subject:</strong> {{ $subjectRequest->subject }}</p>
    <p>Please review and take the necessary action.</p>
</body>
</html>
