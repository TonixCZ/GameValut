<?php
require_once __DIR__ . '/includes/all_games_logic.php';
require_once __DIR__ . '/includes/categories.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Games | GameVault</title>
  <meta name="description" content="Browse all games on GameVault. Discover, filter, and review the best games across all genres and platforms.">
  <meta name="keywords" content="GameVault, all games, browse games, game list, reviews, discover, filter, genres, platforms, ratings">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/all_games.css" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="icon" type="image/png" href="/assets/images/logo.png">
</head>
<body>
<?php include 'layout/header.php'; ?>

<div class="container py-5">
  <section class="mb-5">
    <h1 class="section-title text-center mb-4">🎲 All Games</h1>
    <form class="row g-3 align-items-end justify-content-center mb-4 filter-bar" method="get">
      <div class="col-md-4 position-relative">
        <input type="search" name="search" id="game-search" class="form-control search-input" placeholder="Search games by name..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
        <div id="search-suggestions" class="list-group position-absolute w-100" style="z-index:1000; display:none;"></div>
      </div>
      <div class="col-md-3">
        <select name="category" class="form-select category-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Filter</button>
      </div>
    </form>
    <div class="games-list">
    <?php if (empty($games)): ?>
        <div class="alert alert-warning text-center my-4">
            No games found in this category.
        </div>
    <?php else: ?>
        <div class="row g-4">
          <?php foreach ($games as $game): ?>
            <div class="col-md-4 col-lg-3">
              <a href="/game_detail?id=<?= (int)$game['id'] ?>" class="game-card-link text-decoration-none text-reset">
                <div class="card game-card h-100">
                  <?php $imagePath = 'uploads/games/' . ($game['image'] ?? 'default.jpg'); ?>
                  <img src="<?= htmlspecialchars($imagePath) ?>" class="card-img-top" alt="<?= htmlspecialchars($game['title']) ?>">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1">
                      <?= htmlspecialchars($game['title']) ?>
                    </h5>
                    <div class="mb-2">
                      <span class="badge rating-badge"><?= $game['avg_rating'] > 0 ? round($game['avg_rating'], 2) . ' ★' : 'No rating' ?></span>
                      <span class="badge bg-secondary"><?= $game['review_count'] ?> reviews</span>
                    </div>
                    <div class="mb-2">
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($game['platform']) ?></span>
                    </div>
                    <p class="card-text mb-2">
                      <?= htmlspecialchars(mb_strimwidth($game['description'], 0, 40, '...')) ?>
                    </p>
                    <div class="mt-auto">
                      <span class="badge bg-success price-badge">
                        <?= ($game['price'] == 0) ? 'FREE' : '$' . number_format($game['price'], 2) ?>
                      </span>
                      <?php
                        $catArr = array_filter(array_map('trim', explode(',', $game['categories'])));
                        $catCount = count($catArr);
                      ?>
                      <?php if ($catCount > 0): ?>
                        <span class="badge bg-dark ms-1"><?= htmlspecialchars($catArr[0]) ?></span>
                        <?php if ($catCount > 1): ?>
                          <span class="badge bg-dark ms-1"><?= htmlspecialchars($catArr[1]) ?></span>
                        <?php endif; ?>
                        <?php if ($catCount > 2): ?>
                          <span class="badge bg-secondary ms-1">+<?= $catCount - 2 ?></span>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include 'layout/footer.php'; ?>
<script src="includes/search_suggestions.js"></script>
</body>
</html>