<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

function sendVerificationEmail(string $toEmail, string $toName, string $token): bool {
    $mail = new PHPMailer(true);
    try {
        // â”€â”€ SMTP configuration â”€â”€
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dailybread2k25@gmail.com';  // â† replace with your Gmail
        $mail->Password   = 'ljphuqiijpdjkprh';      // â† replace with Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('dailybread2k25@gmail.com', 'The DailyBread');
        $mail->addAddress($toEmail, $toName);

        $link = "http://localhost/BakeryOrderingSystem/PHP/verify.php?token=" . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your DailyBread account';
        $mail->Body    = "
            <div style='font-family:Segoe UI,sans-serif;max-width:480px;margin:0 auto;padding:2rem;'>
                <h2 style='color:#553423;'>Welcome to The DailyBread, {$toName}!</h2>
                <p style='color:#555;'>Thank you for signing up. Please verify your email address by clicking the button below.</p>
                <a href='{$link}'
                style='display:inline-block;margin:1.5rem 0;padding:12px 28px;
                        background:#96715e;color:white;text-decoration:none;
                        border-radius:8px;font-weight:600;font-size:1rem;'>
                    Verify Email
                </a>
                <p style='color:#999;font-size:0.85rem;'>This link expires in <strong>24 hours</strong>.</p>
                <p style='color:#999;font-size:0.85rem;'>If you did not create an account, you can safely ignore this email.</p>
                <hr style='border:none;border-top:1px solid #f0e8e3;margin-top:2rem;'>
                <p style='color:#ccc;font-size:0.75rem;text-align:center;'>The DailyBread &mdash; Fresh from the oven, straight to your heart.</p>
            </div>
        ";
        $mail->AltBody = "Welcome to The DailyBread, {$toName}! Verify your email: {$link} (expires in 24 hours)";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
function sendPasswordResetEmail(string $toEmail, string $toName, string $token): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dailybread2k25@gmail.com';
        $mail->Password   = 'ljphuqiijpdjkprh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('dailybread2k25@gmail.com', 'The DailyBread');
        $mail->addAddress($toEmail, $toName);

        $link = "http://localhost/BakeryOrderingSystem/PHP/reset_password.php?token=" . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your DailyBread password';
        $mail->Body    = "
            <div style='font-family:Segoe UI,sans-serif;max-width:480px;margin:0 auto;padding:2rem;'>
                <h2 style='color:#553423;'>Password Reset Request</h2>
                <p style='color:#555;'>Hi {$toName}, click the button below to reset your password. This link expires in <strong>1 hour</strong>.</p>
                <a href='{$link}'
                style='display:inline-block;margin:1.5rem 0;padding:12px 28px;
                        background:#96715e;color:white;text-decoration:none;
                        border-radius:8px;font-weight:600;font-size:1rem;'>
                    Reset Password
                </a>
                <p style='color:#999;font-size:0.85rem;'>If you did not request this, you can safely ignore this email.</p>
                <hr style='border:none;border-top:1px solid #f0e8e3;margin-top:2rem;'>
                <p style='color:#ccc;font-size:0.75rem;text-align:center;'>The DailyBread &mdash; Fresh from the oven, straight to your heart.</p>
            </div>
        ";
        $mail->AltBody = "Reset your DailyBread password: {$link} (expires in 1 hour)";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
