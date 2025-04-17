# Password Reset Functionality Setup Guide

This guide will help you set up the password reset functionality with OTP verification for the Blood Donation System.

## Files Overview

1. `forgot_password.php` - The page where users request a password reset by entering their email
2. `verify_otp.php` - The page where users enter the OTP sent to their email
3. `reset_password.php` - The page where users create a new password after OTP verification
4. `reset_password_table.sql` - SQL script to create the necessary database table

## Database Setup

1. Open phpMyAdmin or your preferred MySQL client
2. Select your `blood_donation` database
3. Run the following SQL query to create the required table:

```sql
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

Alternatively, you can import the `reset_password_table.sql` file directly.

## Email Configuration

For the OTP functionality to work, you need to configure your PHP environment to send emails:

1. Open your `php.ini` file (usually located in your PHP installation directory)
2. Set up the mail configuration:

```
[mail function]
SMTP = smtp.gmail.com (or your SMTP server)
smtp_port = 587 (or appropriate port)
sendmail_from = your-email@gmail.com
sendmail_path = "C:\xampp\sendmail\sendmail.exe" -t
```

3. Open `C:\xampp\sendmail\sendmail.ini` and configure:

```
[sendmail]
smtp_server=smtp.gmail.com (or your SMTP server)
smtp_port=587 (or appropriate port)
auth_username=your-email@gmail.com
auth_password=your-app-password
force_sender=your-email@gmail.com
```

4. Restart your Apache server

Note: If you're using Gmail, you'll need to create an "App Password" in your Google account security settings.

## Workflow

1. User clicks "Forgot Password" on the login page
2. User enters their email on the forgot_password.php page
3. System generates a 6-digit OTP, stores it in the database, and sends it to the user's email
4. User enters the OTP on the verify_otp.php page
5. If the OTP is valid, user is redirected to reset_password.php to set a new password
6. After setting a new password, user is redirected back to login.php

## Security Features

- OTP expires after 2 minutes
- Only one active OTP per email (previous tokens are deleted)
- CSRF protection on all forms
- Password requirements (minimum 6 characters)
- Secure password hashing using PHP's password_hash function

## Troubleshooting

### OTP emails not being sent
- Check your mail configuration in php.ini and sendmail.ini
- Make sure your SMTP server allows sending from your application
- Verify that the mail/send.php file is properly included

### Database errors
- Check that the password_resets table was created correctly
- Ensure your database connection details are correct in includes/db.php

### Session issues
- Make sure session_start() is called at the beginning of each file
- Check that session variables are being set and retrieved correctly 