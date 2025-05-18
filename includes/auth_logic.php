<?php

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once __DIR__ . '/../config.php';

$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'] ?? null;
            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid email or password.";
        }
    } elseif (isset($_POST['register'])) {
        $nickname = trim($_POST['nickname']);
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Kontrola unikátnosti nicku a emailu
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR nickname = ?");
        $stmt->execute([$email, $nickname]);
        if ($stmt->fetch()) {
            $message = "Email or nickname is already taken.";
        } elseif ($password !== $confirmPassword) {
            $message = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nickname, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nickname, $firstName, $lastName, $email, $hashedPassword]);
                $message = "Registration successful. You can now log in.";
                $success = true;
            } catch (PDOException $e) {
                $message = "Registration failed: " . $e->getMessage();
            }
        }
    }
}