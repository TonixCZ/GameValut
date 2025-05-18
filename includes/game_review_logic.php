<?php


if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$error = '';
$edit_review = null;

// Přidání nové recenze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $game_id = (int)($_POST['game_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if (!$user_id) {
        header('Location: authentication.php');
        exit;
    }
    if ($rating < 1 || $rating > 5 || $comment === '') {
        $error = 'Please provide a rating (1-5) and a comment.';
    } elseif (mb_strlen($comment) < 30) {
        $error = "Your review must be at least 30 characters long.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, game_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $game_id, $rating, $comment]);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Review created successfully!'];
        header("Location: game_detail.php?id=$game_id#reviews-container");
        exit;
    }
}

// Úprava recenze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review'])) {
    $review_id = (int)($_POST['review_id'] ?? 0);
    $game_id = (int)($_POST['game_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $review = $stmt->fetch();
    if (!$review) {
        $error = 'Review not found or permission denied.';
    } elseif ($rating < 1 || $rating > 5 || $comment === '') {
        $error = 'Please provide a rating (1-5) and a comment.';
    } elseif (mb_strlen($comment) < 30) {
        $error = "Your review must be at least 30 characters long.";
    } else {
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$rating, $comment, $review_id]);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Review updated successfully!'];
        header("Location: game_detail.php?id=$game_id#review-form-container");
        exit;
    }
}

// Smazání recenze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = (int)($_POST['review_id'] ?? 0);
    $game_id = (int)($_POST['game_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $review = $stmt->fetch();
    if ($review) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Review deleted successfully!'];
    }
    header("Location: game_detail.php?id=$game_id#reviews-container");
    exit;
}

// Pokud je požadavek na editaci GETem, načti recenzi do formuláře
if (isset($_GET['edit_review']) && $user_id) {
    $review_id = (int)$_GET['edit_review'];
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $edit_review = $stmt->fetch();
}
?>