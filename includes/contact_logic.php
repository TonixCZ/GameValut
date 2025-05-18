<?php

$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you can send email or save to DB, for now just simulate success
        $sent = true;
    } else {
        $error = "Please fill in all fields with a valid email.";
    }
}