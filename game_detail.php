<?php
session_start();
require_once 'config.php';

// Získání ID hry z GET
$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$game_id) die('Game not found.');

// Načtení detailu hry
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();
if (!$game) die('Game not found.');

// Připojení logiky recenzí
include __DIR__ . '/includes/game_review_logic.php';

// Načtení recenzí a průměrného hodnocení
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.game_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$game_id]);
$reviews = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE game_id = ?");
$stmt->execute([$game_id]);
$average_rating = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($game['title']) ?> | GameVault</title>
    <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($game['description']), 0, 160)) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($game['title']) ?> | GameVault">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags($game['description']), 0, 160)) ?>">
    <?php if (!empty($game['image'])): ?>
        <meta property="og:image" content="/uploads/games/<?= htmlspecialchars($game['image']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/styles.css" />
    <link rel="stylesheet" href="styles/game_detail.css" />
    <link rel="stylesheet" href="styles/footer.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <style>
    .blink {
        animation: blink-animation 1s steps(2, start) 3;
        background: #ffe066;
        color: #222 !important;
        border-radius: 8px;
        padding: 0.5em 0.5em;
    }
    @keyframes blink-animation {
        to { visibility: hidden; }
    }
    </style>
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

    <div class="section-title mt-5">
        <h3>Reviews</h3>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div id="reviews-container">
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card <?= (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']) ? 'my-review' : '' ?>" data-review-id="<?= $review['id'] ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="username">@<?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></span>
                        <span class="text-warning"><?= str_repeat("★", (int)$review['rating']) ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <small class="text-muted"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>

                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                        <div class="mt-2">
                            <form method="get" action="" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $game_id ?>">
                                <input type="hidden" name="edit_review" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-primary">Edit</button>
                            </form>
                            <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <input type="hidden" name="game_id" value="<?= $game_id ?>">
                                <button type="submit" name="delete_review" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No reviews yet.</p>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($edit_review): ?>
                <h4 id="review-form-container" class="blink">Edit Your Review</h4>
                <form method="post" action="">
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
</div>

<script>
    // Alert auto-hide
    setTimeout(() => {
        const alert = document.getElementById('alert-message');
        if(alert) alert.classList.remove('show');
    }, 3500);

    // Scroll & blink to edit form if editing
    <?php if ($edit_review): ?>
    window.onload = function() {
        const form = document.getElementById('review-form-container');
        if(form) {
            form.scrollIntoView({behavior: "smooth", block: "center"});
            form.classList.add('blink');
            setTimeout(() => form.classList.remove('blink'), 1500);
        }
    };
    <?php endif; ?>

    // Show/hide new review form
    document.getElementById('write-review-btn')?.addEventListener('click', () => {
        const formContainer = document.getElementById('review-form-container');
        if (formContainer.style.display === 'none' || !formContainer.style.display) {
            formContainer.style.display = 'block';
        } else {
            formContainer.style.display = 'none';
        }
    });

    document.querySelectorAll('.star-rating').forEach(function(starRating) {
        const stars = starRating.querySelectorAll('.star');
        const inputId = starRating.dataset.input;
        const input = document.getElementById(inputId);
        let currentValue = parseFloat(starRating.dataset.value) || 0;

        function setStars(value) {
            stars.forEach((star, i) => {
                star.classList.remove('filled', 'half');
                if (value >= i + 1) {
                    star.classList.add('filled');
                } else if (value > i && value < i + 1) {
                    star.classList.add('half');
                }
            });
        }

        setStars(currentValue);

        stars.forEach((star, i) => {
            star.addEventListener('mousemove', function(e) {
                const rect = star.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                let value = i + (percent >= 0.5 ? 1 : 0.5);
                setStars(value);
            });
            star.addEventListener('mouseleave', function() {
                setStars(currentValue);
            });
            star.addEventListener('click', function(e) {
                const rect = star.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                currentValue = i + (percent >= 0.5 ? 1 : 0.5);
                setStars(currentValue);
                if (input) input.value = currentValue;
            });
        });
    });

    document.querySelectorAll('form.review-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const textarea = form.querySelector('textarea[name="comment"]');
            if (textarea && textarea.value.trim().length < 30) {
                alert("Your review must be at least 30 characters long.");
                textarea.focus();
                e.preventDefault();
            }
        });
    });
</script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
