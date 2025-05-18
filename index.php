<?php
include('config.php');

// Featured games s průměrným hodnocením > 4.3, rotace po 3 hodinách
$featuredGamesStmt = $pdo->prepare("
    SELECT g.*, AVG(r.rating) as avg_rating
    FROM games g
    JOIN reviews r ON g.id = r.game_id
    GROUP BY g.id
    HAVING avg_rating >= 4.3
    ORDER BY RAND()
    LIMIT 9
");
$featuredGamesStmt->execute();
$featuredGames = $featuredGamesStmt->fetchAll(PDO::FETCH_ASSOC);

// Novinky/tipy (posledních 5)
$newsStmt = $pdo->prepare("SELECT n.*, u.nickname AS author FROM news n LEFT JOIN users u ON n.author_id = u.id ORDER BY n.created_at DESC LIMIT 5");
$newsStmt->execute();
$newsList = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Game Review - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/styles.css" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/footer.css" />

</head>
<body>
<?php include 'layout/header.php'; ?>

<div class="container py-5">
  <!-- Hero -->
  <section class="hero text-center mb-5 p-5">
    <h1 class="display-4">🎮 Welcome to Game Review Central</h1>
    <p class="lead">Your ultimate destination for game reviews, ratings and gaming news.</p>
  </section>

  <!-- Featured Games Carousel -->
  <section class="mb-5">
    <h2 class="section-title text-center">⭐ Top Rated Games</h2>
    <div id="featuredGamesCarousel" class="carousel slide featured-carousel" data-bs-ride="carousel" data-bs-interval="10800000">
      <div class="carousel-inner">
        <?php foreach (array_chunk($featuredGames, 3) as $idx => $gamesRow): ?>
          <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
            <div class="row g-4 justify-content-center">
              <?php foreach ($gamesRow as $game): ?>
                <div class="col-md-4">
                  <div class="card card-featured h-100">
                    <?php
                      $imagePath = 'uploads/games/' . ($game['image'] ?? 'default.jpg');
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" class="card-img-top" alt="<?= htmlspecialchars($game['title']) ?>">
                    <div class="card-body">
                      <h5 class="card-title">
                        <a href="game_detail.php?id=<?= (int)$game['id'] ?>" class="text-light text-decoration-none">
                          <?= htmlspecialchars($game['title']) ?>
                        </a>
                      </h5>
                      <p class="mb-1"><strong>Rating:</strong> <?= round($game['avg_rating'], 2) ?> ★</p>
                      <p class="mb-1">
                        <strong>Price:</strong>
                        <?= ($game['price'] == 0) ? 'FREE' : '$' . number_format($game['price'], 2) ?>
                      </p>
                      <p class="card-text"><?= htmlspecialchars(mb_strimwidth($game['description'], 0, 80, '...')) ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($featuredGames) > 3): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#featuredGamesCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#featuredGamesCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      <?php endif; ?>
    </div>
    <div class="text-center mt-3">
      <a href="all_games.php" class="btn btn-outline-light">See All Games</a>
    </div>
  </section>

  <!-- News & Tips Section -->
  <section class="mb-5">
    <h2 class="section-title text-center">📰 News & Daily Tips</h2>
    <div class="row justify-content-center">
      <?php foreach ($newsList as $news): ?>
        <div class="col-md-6 col-lg-5">
          <div class="news-block mb-4 d-flex flex-column h-100">
            <div class="d-flex align-items-center mb-2">
              <span class="badge">Tip</span>
              <h5 class="mb-0"><?= htmlspecialchars($news['title']) ?></h5>
            </div>
            <p class="mb-2"><?= nl2br(htmlspecialchars(mb_strimwidth($news['content'], 0, 180, '...'))) ?></p>
            <div class="news-meta">
              <?= date('d.m.Y H:i', strtotime($news['created_at'])) ?>
              <?php if ($news['author']): ?> | <span>by <?= htmlspecialchars($news['author']) ?></span><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($newsList)): ?>
        <div class="col-12 text-center text-muted">No news yet.</div>
      <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
      <div class="text-center mt-3">
        <a href="admin_news.php" class="btn btn-outline-warning">Add News / Tip</a>
      </div>
    <?php endif; ?>
  </section>

  <!-- Join Us -->
  <section class="join-us mt-5 p-5 text-center">
    <h2 class="mb-3">Join Our Gaming Community</h2>
    <p class="mb-4">Sign up to review games, save favorites, and get exclusive offers.</p>
    <a href="authentication.php" class="btn btn-outline-light btn-lg">Join Now</a>
  </section>
</div>

<?php include('layout/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
