<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Získání ID hry z GET
$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
/**
 * Checks if the $game_id variable is set and valid.
 * If $game_id is not set or is falsy, terminates the script and outputs 'Game not found.'.
 */
if (!$game_id) die('Game not found.');

// Načtení detailu hry
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();
if (!$game) die('Game not found.');

// Připojení logiky recenzí
include __DIR__ . '/includes/game_review_logic.php';
require_once __DIR__ . '/includes/game_detail.logic.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($game['title']) ?> | GameVault</title>
    <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($game['description']), 0, 160)) ?>">
    <meta name="keywords" content="GameVault, <?= htmlspecialchars($game['title']) ?>, game detail, reviews, platform, genre, rating, <?= htmlspecialchars($game['platform']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($game['title']) ?> | GameVault">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags($game['description']), 0, 160)) ?>">
    <?php if (!empty($game['image'])): ?>
        <meta property="og:image" content="/uploads/games/<?= htmlspecialchars($game['image']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/styles.css" />
    <link rel="stylesheet" href="styles/game_detail.css?v=2" />
    <link rel="stylesheet" href="styles/footer.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
</head>
<body class="bg-dark text-light">

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <?php if (!empty($_SESSION['alert'])): ?>
        <div class="alert alert-<?= $_SESSION['alert']['type'] ?> alert-dismissible fade show text-center" role="alert" id="alert-message">
            <?= htmlspecialchars($_SESSION['alert']['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <!-- Nové uspořádání: obrázek nahoře, info pod ním, vše širší -->
    <div class="game-detail-info mx-auto mb-4" style="max-width: 700px;">
        <?php
        $imagePath = 'uploads/games/' . ($game['image'] ?? 'default.jpg');
        ?>
        <img src="<?= htmlspecialchars($imagePath) ?>" class="img-fluid game-image mb-4" alt="Game image" style="max-width: 420px; max-height: 420px; display: block; margin-left: auto; margin-right: auto;">
        <h1 class="game-detail-title mb-3"><?= htmlspecialchars($game['title']) ?></h1>
        <div class="game-detail-price mb-2">
          <strong>Price:</strong>
          <?= ($game['price'] == 0) ? 'FREE' : '$' . number_format($game['price'], 2) ?>
        </div>
        <div class="game-detail-platform mb-2"><strong>Platform:</strong> <?= htmlspecialchars($game['platform']) ?></div>
        <div class="game-detail-rating mb-3"><strong>Average Rating:</strong>
            <?= $average_rating !== null ? round($average_rating, 2) . " ★" : 'Not rated yet' ?>
        </div>
        <div class="game-detail-description mb-0">
            <?= nl2br(htmlspecialchars($game['description'])) ?>
        </div>

        <?php
        $catArr = array_filter(array_map('trim', explode(',', $game['categories'])));
        ?>
        <?php if ($catArr): ?>
          <div class="game-detail-categories mb-2">
            <strong>Categories:</strong>
            <?php foreach ($catArr as $cat): ?>
              <span class="badge bg-dark"><?= htmlspecialchars($cat) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
    </div>

    <!-- Nadpis sekce -->
    <div class="section-title mt-5">
        <h3>Reviews</h3>
    </div>
    <!-- Filtr přímo pod nadpisem -->
    <div class="mb-4 d-flex align-items-center gap-2" style="max-width:400px;">
        <label for="sort" class="mb-0 fw-bold text-primary">Sort by:</label>
        <select name="sort" id="sort-reviews" class="form-select form-select-sm" style="width:auto;">
            <option value="newest" <?= (!isset($_GET['sort']) || $_GET['sort']=='newest') ? 'selected' : '' ?>>Newest</option>
            <option value="best" <?= (isset($_GET['sort']) && $_GET['sort']=='best') ? 'selected' : '' ?>>Best rated</option>
            <option value="liked" <?= (isset($_GET['sort']) && $_GET['sort']=='liked') ? 'selected' : '' ?>>Most liked</option>
        </select>
    </div>

    <!-- Výpis recenzí -->
    <div id="reviews-container">
        <?php include __DIR__ . '/includes/reviews_list.php'; ?>
    </div>

    <div class="mt-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Editace recenze -->
            <?php if ($edit_review): ?>
                <h4 id="review-form-container" class="blink">Edit Your Review</h4>
                <form method="post" action="" class="review-form">
                    <input type="hidden" name="review_id" value="<?= $edit_review['id'] ?>">
                    <input type="hidden" name="game_id" value="<?= $game_id ?>">
                    <!-- Pro edit review -->
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-rating" data-value="<?= isset($edit_review) ? (float)$edit_review['rating'] : 0 ?>" data-input="rating-edit">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?= $i ?>"></span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-edit" value="<?= isset($edit_review) ? (float)$edit_review['rating'] : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Your review</label>
                        <textarea name="comment" id="comment" rows="5" class="form-control" required><?= htmlspecialchars($edit_review['comment']) ?></textarea>
                    </div>
                    <button type="submit" name="edit_review" class="btn btn-primary">Save changes</button>
                    <a href="game_detail.php?id=<?= $game_id ?>" class="btn btn-secondary">Cancel</a>
                </form>
            <?php else: ?>
                <button id="write-review-btn" class="btn btn-success mb-3">Write Review</button>
                <div id="review-form-container" style="display:none;">
                    <h4>Leave a Review</h4>
                    <form method="post" action="" class="review-form">
                        <input type="hidden" name="game_id" value="<?= $game_id ?>">
                        <!-- Pro nový review -->
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="star-rating" data-value="0" data-input="rating-new">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?= $i ?>"></span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating-new" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea id="comment" name="comment" rows="4" class="form-control" required minlength="30"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="mt-4">You must <a href="authentication.php">log in</a> to write a review.</p>
        <?php endif; ?>
    </div>

    <!-- Stránkování -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?id=<?= $game_id ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?id=<?= $game_id ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?id=<?= $game_id ?>&sort=<?= htmlspecialchars($sort) ?>&page=<?= $page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
window.GAME_ID = <?= (int)$game_id ?>;
<?php if (isset($edit_review)): ?>window.EDIT_REVIEW = true;<?php endif; ?>
</script>
<script src="js/game_detail.js"></script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
