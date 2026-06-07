<?php

/**
 * ARMAS Email Mailer using PHPMailer
 * Configure SMTP settings for your email provider
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (adjust path as needed)
// require 'vendor/autoload.php';

// For standalone usage without Composer, use inline class
// This is a simplified version - in production, install PHPMailer via Composer

function send_email($to_email, $to_name, $subject, $body)
{
    // SMTP Configuration - Update these with your SMTP settings
    $smtp_host = 'smtp.gmail.com';
    $smtp_username = 'armasopap@gmail.com'; // Change this
    $smtp_password = 'quhx csom mwtw bsrz';    // Change this - use App Password for Gmail
    $smtp_secure = 'tls';
    $smtp_port = 587;
    $from_email = 'armasopap@gmail.com';
    $from_name = 'ARMAS System';

    // For development/testing, you can use a simple mail() fallback
    // Uncomment below to use PHP's built-in mail() instead of SMTP
    /*
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $from_name <$from_email>" . "\r\n";
    return mail($to_email, $subject, $body, $headers);
    */

    // Try to use PHPMailer if available
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port = $smtp_port;
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to_email, $to_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    // Fallback to simple mail()
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: $from_name <$from_email>\r\n";
    return mail($to_email, $subject, $body, $headers);
}

function send_otp_email($email, $name, $otp)
{
    $subject = 'ARMAS — Verify Your Email';
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'DM Sans', Arial, sans-serif; background: #f5f6fa; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; }
            .logo { text-align: center; margin-bottom: 20px; }
            .logo-text { font-family: 'Playfair Display', serif; font-size: 24px; color: #1A3A6B; font-weight: bold; }
            h2 { color: #1A3A6B; margin-bottom: 20px; }
            .otp-code { font-family: 'JetBrains Mono', monospace; font-size: 32px; color: #1A3A6B; background: #FFF9E6; padding: 15px 30px; border-radius: 8px; text-align: center; letter-spacing: 5px; }
            .footer { margin-top: 30px; text-align: center; color: #6B7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='logo'>
                <span class='logo-text'>ARMAS</span>
            </div>
            <h2>Welcome to ARMAS</h2>
            <p>Dear $name,</p>
            <p>Your verification code is:</p>
            <div class='otp-code'>$otp</div>
            <p>This code expires in 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
            <div class='footer'>
                <p>Protecting Every Filipino, Every Mile Away</p>
                <p>&copy; 2025 ARMAS — Assistance and Repatriation Management and Action System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    return send_email($email, $name, $subject, $body);
}

function send_welcome_email($email, $name, $temp_password)
{
    $subject = 'Welcome to ARMAS — Your Account Details';
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'DM Sans', Arial, sans-serif; background: #f5f6fa; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; }
            .logo { text-align: center; margin-bottom: 20px; }
            .logo-text { font-family: 'Playfair Display', serif; font-size: 24px; color: #1A3A6B; font-weight: bold; }
            h2 { color: #1A3A6B; margin-bottom: 20px; }
            .credentials { background: #FFF9E6; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .credentials strong { color: #1A3A6B; }
            .footer { margin-top: 30px; text-align: center; color: #6B7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='logo'>
                <span class='logo-text'>ARMAS</span>
            </div>
            <h2>Welcome to ARMAS</h2>
            <p>Dear $name,</p>
            <p>Your ARMAS account has been created by your agency.</p>
            <div class='credentials'>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Temporary Password:</strong> $temp_password</p>
            </div>
            <p>Please log in and change your password immediately.</p>
            <p><em>Protecting Every Filipino, Every Mile Away</em></p>
            <div class='footer'>
                <p>&copy; 2025 ARMAS — Assistance and Repatriation Management and Action System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    return send_email($email, $name, $subject, $body);
}

function send_case_notification($email, $ofw_name, $case_number, $status)
{
    $subject = "ARMAS — Case $case_number Status Update";
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'DM Sans', Arial, sans-serif; background: #f5f6fa; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; }
            .logo { text-align: center; margin-bottom: 20px; }
            .logo-text { font-family: 'Playfair Display', serif; font-size: 24px; color: #1A3A6B; font-weight: bold; }
            h2 { color: #1A3A6B; margin-bottom: 20px; }
            .status { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
            .status-pending { background: #FEF3C7; color: #92400E; }
            .status-in_process { background: #DBEAFE; color: #1E40AF; }
            .status-resolved { background: #D1FAE5; color: #065F46; }
            .status-closed { background: #FEE2E2; color: #991B1B; }
            .footer { margin-top: 30px; text-align: center; color: #6B7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='logo'>
                <span class='logo-text'>ARMAS</span>
            </div>
            <h2>Case Status Update</h2>
            <p>Dear $ofw_name,</p>
            <p>Your repatriation request <strong>$case_number</strong> status has been updated to:</p>
            <p><span class='status status-$status'>" . strtoupper(str_replace('_', ' ', $status)) . "</span></p>
            <p>Please log in to ARMAS to view details.</p>
            <div class='footer'>
                <p>Protecting Every Filipino, Every Mile Away</p>
                <p>&copy; 2025 ARMAS — Assistance and Repatriation Management and Action System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    return send_email($email, $ofw_name, $subject, $body);
}
