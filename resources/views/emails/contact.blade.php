<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">

@if($type === 'owner')
    <h2>New Contact Form Submission</h2>
    <p>You have received a new message from your landing page:</p>
    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eee;">Name:</td>
            <td style="border-bottom: 1px solid #eee;">{{ $data['name'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eee;">Phone:</td>
            <td style="border-bottom: 1px solid #eee;">{{ $data['phone'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; border-bottom: 1px solid #eee;">Email:</td>
            <td style="border-bottom: 1px solid #eee;">{{ $data['email'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; vertical-align: top;">Comment:</td>
            <td>{{ nl2br(e($data['comment'])) }}</td>
        </tr>
    </table>
@else
    <h2>Thank You, {{ $data['name'] }}!</h2>
    <p>We have received your message and will get back to you soon.</p>
    <p><strong>Your message:</strong></p>
    <blockquote style="border-left: 3px solid #ccc; padding-left: 12px; color: #666;">
        {{ nl2br(e($data['comment'])) }}
    </blockquote>
@endif

    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 12px; color: #999;">This is an automated message from the Landing Page API.</p>

</body>
</html>
