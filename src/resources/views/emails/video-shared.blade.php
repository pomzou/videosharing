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
            background-color: #4f46e5;
            color: #fff;
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
            background-color: #4f46e5;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .info-box {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
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
        <h1>File Shared With You</h1>
    </div>

    <div class="content">
        <p>Hello,</p>
        <p>{{ $share->videoFile->user->name ?? 'Someone' }} has shared a file with you.</p>

        <div class="info-box">
            <h3>File Details:</h3>
            <ul>
                <li><strong>Title:</strong> {{ $share->videoFile->title }}</li>
                <li><strong>Type:</strong>
                    {{ strtoupper(pathinfo($share->videoFile->original_name, PATHINFO_EXTENSION)) }}</li>
                <li><strong>Size:</strong> {{ number_format($share->videoFile->file_size / 1024 / 1024, 2) }} MB</li>
                <li><strong>Access Expires:</strong> {{ $share->expires_at->format('Y-m-d H:i') }}</li>
            </ul>
        </div>

        <p>To access this file, use the secure download link below:</p>
        <a href="{{ route('stream.video', ['shortUrl' => $share->short_url]) }}" class="button">
            Access File
        </a>

        <div class="info-box mt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Important Notes:</h4>
            <ul class="text-sm text-gray-600">
                <li>• This is a secure, time-limited download link</li>
                <li>• The link will expire on {{ $share->expires_at->format('Y-m-d H:i') }}</li>
                <li>• Please download the file before the expiration date</li>
            </ul>
        </div>

        <div class="warning">
            <h4>Important Security Information:</h4>
            <ul>
                <li>This link is unique to your email address and will expire on
                    {{ $share->expires_at->format('Y-m-d H:i') }}.</li>
                <li>Do not share this link with others. Each viewer should receive their own secure link.</li>
                <li>All access attempts are logged for security purposes.</li>
            </ul>
        </div>

        @if ($share->videoFile->description)
            <div class="info-box">
                <h3>Message from the sender:</h3>
                <p>{{ $share->videoFile->description }}</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you have any questions or did not expect to receive this file, please contact the sender.</p>
        <p>For security concerns, please report to our support team.</p>
    </div>
</body>

</html>
