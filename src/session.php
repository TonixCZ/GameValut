<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$firstName = $isLoggedIn ? $_SESSION['first_name'] : '';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$searchQuery = $_GET['search'] ?? '';
?>
