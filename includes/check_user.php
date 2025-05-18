<?php

require_once __DIR__ . '/../config.php';

$exclude = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;

if (isset($_GET['nickname'])) {
    $sql = "SELECT id FROM users WHERE nickname = ?";
    $params = [$_GET['nickname']];
    if ($exclude) {
        $sql .= " AND id != ?";
        $params[] = $exclude;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo $stmt->fetch() ? "Nickname is already taken." : "";
    exit;
}
if (isset($_GET['email'])) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $params = [$_GET['email']];
    if ($exclude) {
        $sql .= " AND id != ?";
        $params[] = $exclude;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo $stmt->fetch() ? "Email is already registered." : "";
    exit;
}
?>