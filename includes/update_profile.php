<?php
session_start();
require_once __DIR__ . '/../config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['avatar']['tmp_name'];
    $fileName = basename($_FILES['avatar']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) {
        die("Invalid file type.");
    }

    $uploadDir = __DIR__ . '/../uploads/users/'; // absolutní cesta pro ukládání
    $relativeDir = 'uploads/users/'; // relativní cesta pro zobrazování

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = uniqid('avatar_', true) . "." . $ext;
    $targetPath = $uploadDir . $newFileName; // ukládání na disk

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        die("Failed to upload avatar.");
    }

    // Do databáze ukládej pouze název souboru:
    $avatarDbValue = $newFileName;
}

// Získání údajů z formuláře
$nickname = trim($_POST['nickname'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Kontrola unikátnosti nicku a emailu (kromě sebe)
$stmt = $pdo->prepare("SELECT id FROM users WHERE (nickname = ? OR email = ?) AND id != ?");
$stmt->execute([$nickname, $email, $user_id]);
if ($stmt->fetch()) {
    die("Nickname or email is already taken.");
}

// Pokud je zadáno nové heslo, změň ho
if (!empty($_POST['password'])) {
    if ($_POST['password'] !== $_POST['password_confirm']) {
        die("Passwords do not match.");
    }
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
}

// Aktualizace ostatních údajů (včetně avataru, pokud byl změněn)
if (isset($newFileName)) {
    $stmt = $pdo->prepare("UPDATE users SET nickname = ?, first_name = ?, last_name = ?, email = ?, avatar = ? WHERE id = ?");
    $stmt->execute([$nickname, $first_name, $last_name, $email, $newFileName, $user_id]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET nickname = ?, first_name = ?, last_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$nickname, $first_name, $last_name, $email, $user_id]);
}

header("Location: ../profile.php");
exit();
