<?php
require_once __DIR__ . '/config.php';
session_start();

$token = $_GET['token'] ?? '';
if ($token) {
    $stmt = $pdo->prepare("SELECT id, registered_at FROM users WHERE verify_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $regTime = strtotime($user['registered_at']);
        if (time() - $regTime < 172800) { // 48 hours = 172800 seconds
            $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?")->execute([$user['id']]);
            $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Your account has been successfully verified. You can now log in.'];
        } else {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user['id']]);
            $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'The verification link has expired. Registration was cancelled.'];
        }
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Invalid or already used verification link.'];
    }
} else {
    $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Verification token is missing.'];
}
header("Location: authentication.php");
exit;