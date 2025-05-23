<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Avatar upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['avatar']['tmp_name'];
    $fileName = basename($_FILES['avatar']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) {
        $_SESSION['profile_error'] = "Invalid file type.";
        header("Location: ../settings.php");
        exit();
    }

    $uploadDir = __DIR__ . '/../uploads/users/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = uniqid('avatar_', true) . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        $_SESSION['profile_error'] = "Failed to upload avatar.";
        header("Location: ../settings.php");
        exit();
    }
    $avatarDbValue = $newFileName;
}

// Get form data
$nickname = trim($_POST['nickname'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Check uniqueness of nickname and email (except self)
$stmt = $pdo->prepare("SELECT id FROM users WHERE (nickname = ? OR email = ?) AND id != ?");
$stmt->execute([$nickname, $email, $user_id]);
if ($stmt->fetch()) {
    $_SESSION['profile_error'] = "Nickname or email is already taken.";
    header("Location: ../settings.php");
    exit();
}

// Password change
if (!empty($_POST['password'])) {
    if ($_POST['password'] !== $_POST['password_confirm']) {
        $_SESSION['profile_error'] = "Passwords do not match.";
        header("Location: ../settings.php");
        exit();
    }
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
}

// Update other fields (including avatar if changed)
if (isset($avatarDbValue)) {
    $stmt = $pdo->prepare("UPDATE users SET nickname = ?, first_name = ?, last_name = ?, email = ?, avatar = ? WHERE id = ?");
    $stmt->execute([$nickname, $first_name, $last_name, $email, $avatarDbValue, $user_id]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET nickname = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$nickname, $first_name, $last_name, $email, $user_id]);
}

$_SESSION['profile_success'] = "Profile updated successfully!";
header("Location: ../settings.php");
exit();
