<?php
require_once __DIR__ . '/send_mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function sendVerificationMail($to, $nickname, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'your_mail_host';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_password'; // Your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('your_email@example.com', 'Game Valut');
        $mail->addAddress($to, $nickname);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to GameValut! Please confirm your registration';

        $verifyUrl = "https://your-domain.com/verify.php?token=$token";
        $logoUrl = "https://your-domain.com/assets/images/EmailLogo.png";

        $mail->Body = "
            <div style='text-align:center;'>
                <img src='$logoUrl' alt='GameValut Logo' style='max-width:180px; margin-bottom:20px;'>
                <h2>Welcome to GameValut, $nickname!</h2>
                <p>We're excited to have you join our gaming community.<br>
                To continue and complete your registration, please click the link below:</p>
                <p><a href='$verifyUrl' style='display:inline-block;padding:12px 24px;background:#ff6b81;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;'>Confirm Registration</a></p>
                <p style='color:#888;font-size:13px;margin-top:30px;'>If you did not register, you can ignore this email.<br>
                This link is valid for 48 hours.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}