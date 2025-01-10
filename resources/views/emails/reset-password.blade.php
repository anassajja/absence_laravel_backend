<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .email-header img {
            max-width: 150px;
        }
        .email-body {
            padding: 20px;
            text-align: center;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.6;
        }
        .reset-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #4CAF50;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }
        .reset-button:hover {
            background-color: #45a049;
        }
        .email-footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
        .expiration-info {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="email-container">
        <div class="email-header">
            <!-- Replace the src with your logo URL -->
            <img src="{{ url('images/logo.png') }}" alt="Logo">
        </div>

        <div class="email-body">
            <h2>Reset Your Password</h2>
            <p>You are receiving this email because we received a password reset request for your account at {{ config('app.name') }}.</p>
            <p>Click the button below to reset your password:</p>
            
            <!-- Reset password button -->
            <a href="{{ $resetLink }}" class="reset-button">Reset Password</a>

            <p>If you did not request a password reset, no further action is required.</p>
            
            <!-- Expiration Information -->
            <div class="expiration-info">
                <p>This reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.</p>
            </div>
        </div>

        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Attendance Management System. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
