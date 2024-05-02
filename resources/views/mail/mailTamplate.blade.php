<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password Verification</title>
</head>

<body>
    <div style="font-family: Arial, sans-serif; margin: auto; max-width: 600px; padding: 20px;">
        <h2>Change Password Verification</h2>
        <p>Hello {{$data['name']}},</p>
        <p>You are receiving this email because we received a request to change your password.</p>
        <p>Please click the link below to verify your request:</p>
        <p>{{$data['url']}}</p>
        <p>If you did not request a password change, you can safely ignore this email.</p>
        <p>Thank you!</p>
        <p>Best regards,<br>Your Company Name</p>
    </div>
</body>

</html>
