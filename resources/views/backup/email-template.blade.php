<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Database Backup</title>
</head>

<body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;">

    <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">

        <div style="background:#034e7a; color:#fff; padding:20px; text-align:center;">
            <h2 style="margin:0; font-size:24px;">Database Backup Success</h2>
        </div>

        <div style="padding:30px; color:#333;">
            <p style="font-size:16px;">Hello,</p>

            <p style="font-size:15px; line-height:1.6;">
                The automated system has successfully generated a new database backup.
            </p>

            <div style="background:#f9f9f9; padding:15px; border-left: 4px solid #034e7a; margin: 20px 0;">
                <p style="margin:5px 0; font-size:14px; color:#555;"><strong>Date & Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                <p style="margin:5px 0; font-size:14px; color:#555;"><strong>File Name:</strong> {{ basename($filePath) ?? 'backup.sql' }}</p>
            </div>

            <p style="font-size:15px; line-height:1.6;">
                The backup file is attached to this email. Please save it to a secure location.
            </p>

            <p style="font-size:14px; margin-top:30px;">
                Thanks,<br>
                <strong>{{ config('app.name') }} Support</strong>
            </p>
        </div>

        <div style="background:#034e7a; padding:12px; text-align:center; font-size:12px; color:#e0e0e0;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>

</body>

</html>