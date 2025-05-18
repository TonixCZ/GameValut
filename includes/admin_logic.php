<?php

session_start();
require_once __DIR__ . '/../config.php';

// Kontrola admina
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$stmt = $pdo->prepare("SELECT role, first_name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    die("Access denied. You must be an administrator.");
}

// Kategorie pro hry
$allCategories = [
    'Free-to-Play', 'Action', 'Adventure', 'RPG', 'Indie',
    'Multiplayer', 'Singleplayer', 'Co-op', 'Strategy', 'Simulation'
];
$allPlatforms = ['PC', 'Xbox', 'PlayStation', 'Switch', 'Mobile', 'Other'];

// --- Přidání hry ---
$successMsg = $errorMsg = null;
$title = $description = $price = '';
$selectedCategories = $selectedPlatforms = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $image = $_FILES['image'] ?? null;
    $selectedCategories = $_POST['categories'] ?? [];
    $selectedPlatforms = $_POST['platform'] ?? [];

    if (strlen($title) < 2) {
        $errorMsg = "The game title must be at least 2 characters long.";
    } elseif (strlen($description) < 10) {
        $errorMsg = "The game description must be at least 10 characters long.";
    } elseif (!is_numeric($price) || floatval($price) < 0) {
        $errorMsg = "Price must be a positive number or zero.";
    } elseif (!$image || $image['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "You must upload a game image.";
    } elseif (!is_array($selectedCategories) || count($selectedCategories) === 0) {
        $errorMsg = "Please select at least one category.";
    } elseif (!is_array($selectedPlatforms) || count($selectedPlatforms) === 0) {
        $errorMsg = "Please select at least one platform.";
    } else {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $imageMime = mime_content_type($image['tmp_name']);
        if (!in_array($imageMime, $allowedMimeTypes)) {
            $errorMsg = "Unsupported image format. Allowed types: JPEG, PNG, GIF, WEBP.";
        } else {
            $uploadDir = 'uploads/games/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    $errorMsg = "Failed to create upload directory.";
                }
            }
            if (!$errorMsg) {
                $originalExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                $safeTitle = preg_replace('/[^A-Za-z0-9\-]/', '_', strtolower($title));
                $uniqueId = uniqid();
                $newFileName = $safeTitle . '_' . $uniqueId . '.' . $originalExtension;
                $uploadPath = $uploadDir . $newFileName;
                if (move_uploaded_file($image['tmp_name'], $uploadPath)) {
                    $categoriesCSV = implode(',', array_map('trim', $selectedCategories));
                    $platformsCSV = implode(',', array_map('trim', $selectedPlatforms));
                    $stmt = $pdo->prepare("INSERT INTO games (title, description, price, platform, image, categories) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$title, $description, floatval($price), $platformsCSV, $newFileName, $categoriesCSV])) {
                        $successMsg = "Game has been successfully added.";
                        $title = $description = $price = '';
                        $selectedCategories = $selectedPlatforms = [];
                    } else {
                        $errorMsg = "Database error while saving the game.";
                        unlink($uploadPath);
                    }
                } else {
                    $errorMsg = "Error saving the uploaded image file.";
                }
            }
        }
    }
}

// --- Přidání novinky ---
$newsSuccess = $newsError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $newsTitle = trim($_POST['news_title'] ?? '');
    $newsContent = trim($_POST['news_content'] ?? '');
    if (strlen($newsTitle) < 3) {
        $newsError = "Title must be at least 3 characters.";
    } elseif (strlen($newsContent) < 10) {
        $newsError = "Content must be at least 10 characters.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, author_id) VALUES (?, ?, ?)");
        $stmt->execute([$newsTitle, $newsContent, $_SESSION['user_id']]);
        $newsSuccess = "News/tip added!";
    }
}

// --- Mazání novinky ---
if (isset($_POST['delete_news'])) {
    $newsId = (int)$_POST['delete_news'];
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$newsId]);
}

// --- Výpis novinek ---
$stmt = $pdo->query("SELECT n.*, u.nickname AS author FROM news n LEFT JOIN users u ON n.author_id = u.id ORDER BY n.created_at DESC");
$newsList = $stmt->fetchAll();

// --- Mazání uživatele ---
if (isset($_POST['delete_user'])) {
    $del_id = (int)$_POST['delete_user'];
    if ($del_id !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
    }
}

// --- Výpis uživatelů ---
$stmt = $pdo->query("
    SELECT u.id, u.nickname, u.email, u.created_at, u.role,
        (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) AS review_count
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Uložení nové ceny
if (isset($_POST['save_price'], $_POST['game_id'])) {
    $newPrice = floatval($_POST['edit_price']);
    $gameId = (int)$_POST['game_id'];
    $pdo->prepare("UPDATE games SET price = ? WHERE id = ?")->execute([$newPrice, $gameId]);
    $successMsg = "Price updated successfully.";
}

// Mazání hry
if (isset($_POST['delete_game'])) {
    $gameId = (int)$_POST['delete_game'];
    $pdo->prepare("DELETE FROM games WHERE id = ?")->execute([$gameId]);
    $successMsg = "Game deleted successfully.";
}
$price = trim($_POST['price'] ?? '');
if ($price === '' || !is_numeric($price)) {
    $price = 0;
}
?>