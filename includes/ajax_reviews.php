<?php
session_start();
require_once __DIR__ . '/../config.php';

$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$game_id) exit('Game not found.');

$sort = $_GET['sort'] ?? 'newest';
$orderBy = "r.created_at DESC";
if ($sort === 'best') {
    $orderBy = "r.rating DESC, r.created_at DESC";
} elseif ($sort === 'liked') {
    $orderBy = "(SELECT COUNT(*) FROM review_likes l WHERE l.review_id = r.id) DESC, r.created_at DESC";
}
$perPage = 10;
$page = 1;
$offset = 0;

$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.game_id = ? ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute([$game_id]);
$reviews = $stmt->fetchAll();

$reviewIds = array_column($reviews, 'id');
$commentsByReview = [];
if ($reviewIds) {
    $in = implode(',', array_map('intval', $reviewIds));
    $stmt = $pdo->query("SELECT c.*, u.first_name, u.last_name FROM review_comments c JOIN users u ON c.user_id = u.id WHERE c.review_id IN ($in) ORDER BY c.created_at ASC");
    foreach ($stmt->fetchAll() as $row) {
        $commentsByReview[$row['review_id']][] = $row;
    }
}

include __DIR__ . '/reviews_list.php';

