<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8'; // podpora češtiny
            $mail->isSMTP();
            $mail->Host = 'mail.webglobe.cz';
            $mail->SMTPAuth = true;
            $mail->Username = 'info@games-hub.eu';
            $mail->Password = 'Emmicka22.'; // Your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('info@games-hub.eu', 'Game Valut Contact');
            $mail->addAddress('info@games-hub.eu', 'Game Valut'); // kam přijde zpráva

            $mail->isHTML(true);
            $mail->Subject = "Contact Form Message from $name";
            $mail->Body = "
                <h3>New message from Game Valut contact form</h3>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
            ";

            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            $error = "Message could not be sent. Please try again later.";
        }
    }
}