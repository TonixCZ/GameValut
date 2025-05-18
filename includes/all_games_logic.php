<?php

// Zajištění, že session je spuštěna
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/categories.php'; // <-- přidej tento řádek



// Získání filtru a vyhledávání
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// SQL dotaz na hry
$sql = "SELECT g.*, 
            IFNULL(AVG(r.rating), 0) as avg_rating, 
            COUNT(r.id) as review_count 
        FROM games g 
        LEFT JOIN reviews r ON g.id = r.game_id";
$params = [];
$where = [];

if ($search !== '') {
    $where[] = "g.title LIKE ?";
    $params[] = "%$search%";
}
if ($category !== '' && in_array($category, $categories)) {
    $where[] = "FIND_IN_SET(?, g.categories)";
    $params[] = $category;
}
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " GROUP BY g.id ORDER BY avg_rating DESC, g.title ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>