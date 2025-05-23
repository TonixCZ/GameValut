<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_POST['review_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$reviewId = (int)$_POST['review_id'];
$userId = (int)$_SESSION['user_id'];

// Zjisti, jestli uživatel už likenul
$stmt = $pdo->prepare("SELECT 1 FROM review_likes WHERE review_id = ? AND user_id = ?");
$stmt->execute([$reviewId, $userId]);
if ($stmt->fetchColumn()) {
    // Už like existuje, smaž ho (unlike)
    $pdo->prepare("DELETE FROM review_likes WHERE review_id = ? AND user_id = ?")->execute([$reviewId, $userId]);
    $liked = false;
} else {
    // Jinak přidej like
    $pdo->prepare("INSERT IGNORE INTO review_likes (review_id, user_id) VALUES (?, ?)")->execute([$reviewId, $userId]);
    $liked = true;
}
// Vrať aktuální počet like
$count = $pdo->query("SELECT COUNT(*) FROM review_likes WHERE review_id = $reviewId")->fetchColumn();
echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
exit;