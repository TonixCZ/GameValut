<?php
session_start();

// Vyčistí všechna session data
$_SESSION = [];

// Zničí session cookie (pokud existuje)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Zničí session
session_destroy();

// Přesměruje na domovskou stránku nebo přihlašovací
header('Location: /authentication.php');
exit;
