<?php
error_log('EDIT_REVIEW: ' . print_r($_POST, true));


if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$error = '';
$edit_review = null;

// Přidání nové recenze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $game_id = (int)($_POST['game_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($user_id === 0) {
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
    // $user_id už je na začátku souboru

    error_log('EDIT_REVIEW_POST: ' . print_r($_POST, true));

    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $review = $stmt->fetch();

    if (!$review) {
        error_log('EDIT_REVIEW: Review not found for id=' . $review_id . ' and user_id=' . $user_id);
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Review not found or permission denied.'];
    } elseif ($rating < 1 || $rating > 5 || $comment === '') {
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Please provide a rating (1-5) and a comment.'];
    } elseif (mb_strlen($comment) < 30) {
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => "Your review must be at least 30 characters long."];
    } else {
        $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$rating, $comment, $review_id]);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Review updated successfully!'];
    }
    // OPRAVA: pokud není game_id v POST, vezmi ho z recenze
    if (!$game_id && isset($review['game_id'])) $game_id = $review['game_id'];
    // OPRAVA: pokud stále není, nastav na 1 (nebo jinou defaultní hodnotu)
    if (!$game_id) $game_id = 1;
    header("Location: game_detail.php?id=$game_id#review-form-container");
    exit;
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

// Přidání komentáře k recenzi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $review_id = (int)($_POST['review_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($user_id === 0) {
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'You must be logged in to comment.'];
    } elseif ($comment === '') {
        $_SESSION['alert'] = ['type' => 'danger', 'msg' => 'Comment cannot be empty.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO review_comments (review_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$review_id, $user_id, $comment]);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Comment added!'];
    }
    header("Location: game_detail.php?id=$game_id#reviews-container");
    exit;
}

// Smazání komentáře
if (isset($_POST['delete_comment_id'], $_SESSION['user_id'])) {
    $commentId = (int)$_POST['delete_comment_id'];
    // Smaže jen autor nebo admin
    $stmt = $pdo->prepare("DELETE FROM review_comments WHERE id = ? AND (user_id = ? OR ? = 'admin')");
    $stmt->execute([$commentId, $_SESSION['user_id'], $_SESSION['role'] ?? '']);
    $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Comment deleted!'];
    header("Location: game_detail.php?id=$game_id#reviews-container");
    exit;
}

// Editace komentáře
if (isset($_POST['edit_comment_id'], $_POST['edit_comment_text'], $_SESSION['user_id'])) {
    $commentId = (int)$_POST['edit_comment_id'];
    $text = trim($_POST['edit_comment_text']);
    if ($text !== '') {
        $stmt = $pdo->prepare("UPDATE review_comments SET comment = ? WHERE id = ? AND (user_id = ? OR ? = 'admin')");
        $stmt->execute([$text, $commentId, $_SESSION['user_id'], $_SESSION['role'] ?? '']);
        $_SESSION['alert'] = ['type' => 'success', 'msg' => 'Comment updated!'];
    }
    header("Location: game_detail.php?id=$game_id#reviews-container");
    exit;
}
?>