<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: {{ $isExpired ? '#dc3545' : '#ffc107' }};
            color: {{ $isExpired ? '#fff' : '#000' }};
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>
            @if ($isExpired)
                Share Link Expired
            @else
                Share Link Expiring Soon
            @endif
        </h1>
    </div>

    <div class="content">
        <p>Dear {{ $videoFile->user->name }},</p>

        @if ($isExpired)
            <p>The share link for your file "{{ $videoFile->title }}" has expired.</p>
            <p>If you need to share this file again, please generate a new share link from your dashboard.</p>
        @else
            <p>The share link for your file "{{ $videoFile->title }}" will expire in {{ $hoursRemaining }} hours.</p>
            <p>If you want to continue sharing this file, please extend the expiration time or generate a new share
                link.</p>
        @endif

        <p>File Details:</p>
        <ul>
            <li>Title: {{ $videoFile->title }}</li>
            <li>Original Name: {{ $videoFile->original_name }}</li>
            <li>Size: {{ number_format($videoFile->file_size / 1024 / 1024, 2) }} MB</li>
            @if (!$isExpired)
                <li>Expires At: {{ $videoFile->url_expires_at->format('Y-m-d H:i:s') }}</li>
            @endif
        </ul>

        <a href="{{ url('/dashboard') }}" class="button">
            Go to Dashboard
        </a>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you have any questions, please contact support.</p>
    </div>
</body>

</html>
