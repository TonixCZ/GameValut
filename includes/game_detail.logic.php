<?php

// Tento soubor už NEVOLÁ session_start ani nenačítá detail hry ani recenzní logiku

require_once __DIR__ . '/../config.php';

// Očekává, že $game_id už je nastavený a validní

$sort = $_GET['sort'] ?? 'newest';
$orderBy = "r.created_at DESC";
if ($sort === 'best') {
    $orderBy = "r.rating DESC, r.created_at DESC";
} elseif ($sort === 'liked') {
    $orderBy = "(SELECT COUNT(*) FROM review_likes l WHERE l.review_id = r.id) DESC, r.created_at DESC";
}

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Spočítej celkový počet recenzí
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE game_id = ?");
$stmt->execute([$game_id]);
$totalReviews = $stmt->fetchColumn();
$totalPages = ceil($totalReviews / $perPage);

// Výpis recenzí pro stránkování
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.game_id = ? ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute([$game_id]);
$reviews = $stmt->fetchAll();

// Průměrné hodnocení
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE game_id = ?");
$stmt->execute([$game_id]);
$average_rating = $stmt->fetchColumn();

// Komentáře ke všem recenzím
$reviewIds = array_column($reviews, 'id');
$commentsByReview = [];
if ($reviewIds) {
    $in = implode(',', array_map('intval', $reviewIds));
    $stmt = $pdo->query("SELECT c.*, u.first_name, u.last_name FROM review_comments c JOIN users u ON c.user_id = u.id WHERE c.review_id IN ($in) ORDER BY c.created_at ASC");
    foreach ($stmt->fetchAll() as $row) {
        $commentsByReview[$row['review_id']][] = $row;
    }
}