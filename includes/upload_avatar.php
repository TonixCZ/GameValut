<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Zkontroluj, jestli byl nahrán soubor
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    die("Chyba při nahrávání souboru.");
}

// Ověření, že jde o obrázek
$check = getimagesize($_FILES["avatar"]["tmp_name"]);
if ($check === false) {
    die("Soubor není platný obrázek.");
}

// Povolené přípony
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    die("Povoleny jsou pouze soubory: JPG, JPEG, PNG, GIF.");
}

// Unikátní název souboru
$newFileName = uniqid("avatar_") . "." . $extension;
$targetDir = "uploads/users/";
$targetPath = $targetDir . $newFileName;

// Zjisti současný avatar
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentAvatar = $stmt->fetchColumn();

// Nahraj soubor
if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetPath)) {
    die("Nepodařilo se uložit obrázek.");
}

// Smaž starý avatar (kromě default.png)
if ($currentAvatar && $currentAvatar !== 'default.png') {
    $oldPath = $targetDir . $currentAvatar;
    if (file_exists($oldPath)) {
        unlink($oldPath);
    }
}

// Ulož nový avatar do DB
$stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
$stmt->execute([$newFileName, $user_id]);

// Přesměrování zpět na profil
header("Location: profile.php");
exit();
