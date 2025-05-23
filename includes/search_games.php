<?php
require_once __DIR__ . '/../config.php';

$term = trim($_GET['q'] ?? '');
if ($term === '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, title, image FROM games WHERE title LIKE ? ORDER BY title ASC LIMIT 8");
$stmt->execute(['%' . $term . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);