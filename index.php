<?php
include('config.php');

// Smaže návštěvy starší než 3 měsíce
$pdo->exec("DELETE FROM visits WHERE visited_at < (NOW() - INTERVAL 3 MONTH)");

// Logování návštěvy
$pdo->prepare("INSERT INTO visits (visited_at, ip) VALUES (NOW(), ?)")->execute([$_SERVER['REMOTE_ADDR']]);

// Featured games s průměrným hodnocením > 4.3, rotace po 3 hodinách
$featuredGamesStmt = $pdo->prepare("
    SELECT g.*, AVG(r.rating) as avg_rating
    FROM games g
    JOIN reviews r ON g.id = r.game_id
    GROUP BY g.id
    HAVING avg_rating >= 4.1
    ORDER BY RAND()
    LIMIT 9
");
$featuredGamesStmt->execute();
$featuredGames = $featuredGamesStmt->fetchAll(PDO::FETCH_ASSOC);

// Novinky/tipy (posledních 5)
$newsStmt = $pdo->prepare("SELECT n.*, u.nickname AS author FROM news n LEFT JOIN users u ON n.author_id = u.id ORDER BY n.created_at DESC LIMIT 20");
$newsStmt->execute();
$newsList = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

// Poslední 4 NEWS
$newsStmt = $pdo->prepare("SELECT n.*, u.nickname AS author FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE n.type = 'news' ORDER BY n.created_at DESC LIMIT 4");
$newsStmt->execute();
$newsItems = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

// Poslední 4 TIPY
$tipStmt = $pdo->prepare("SELECT n.*, u.nickname AS author FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE n.type = 'tip' ORDER BY n.created_at DESC LIMIT 4");
$tipStmt->execute();
$tipItems = $tipStmt->fetchAll(PDO::FETCH_ASSOC);

function chunkAndWrap($array) {
    $chunks = [];
    $count = count($array);
    if ($count === 0) return [];
    for ($i = 0; $i < $count; $i += 2) {
        $first = $array[$i];
        $second = ($i + 1 < $count) ? $array[$i + 1] : $array[0]; // doplní první, pokud je lichý počet
        $chunks[] = [$first, $second];
    }
    return $chunks;
}
$newsChunks = chunkAndWrap($newsItems);
$tipChunks = chunkAndWrap($tipItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GameVault – Discover, Review & Share the Best Games</title>
  <meta name="description" content="Welcome to GameVault! Discover top-rated games, read reviews, get the latest gaming news and tips, and join our active gaming community.">
  <meta name="keywords" content="GameVault, games, game reviews, gaming news, tips, ratings, community, discover games, best games, join, multiplayer, indie, platform">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/styles.css" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/footer.css" />
  <link rel="icon" type="image/x-icon" href="assets/images/BrowserLogo.ico">
  <link rel="shortcut icon" type="image/x-icon" href="assets/images/BrowserLogo.ico">
  <link rel="apple-touch-icon" href="assets/images/BrowserLogo.ico">
  
</head>
<body>
<?php include 'layout/header.php'; ?>

<?php if (!isset($_SESSION['user_id'])): ?>
  <!-- Sticky CTA bar for mobile -->
  <div class="sticky-cta-bar d-md-none">
    <span>🚀 Join our gaming community!</span>
    <a href="authentication.php" class="btn btn-outline-light btn-sm">Sign In / Register</a>
  </div>
<?php endif; ?>

<div class="container py-5">
  <!-- Hero -->
  <section class="hero text-center mb-5 p-5 fade-in">
    <h1 class="display-4">🎮 Welcome to Game Review Central</h1>
    <p class="lead">Your ultimate destination for game reviews, ratings and gaming news.</p>
    <a href="#featuredGamesCarousel" class="btn btn-primary btn-lg mt-3 smooth-scroll">Explore Top Games</a>
  </section>

  <!-- Trending Tags sekce -->
  <div class="trending-tags fade-in mb-5">
    <span class="tag" onclick="window.location.href='all_games.php?cat=Action'">#Action</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=RPG'">#RPG</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Indie'">#Indie</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Strategy'">#Strategy</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Adventure'">#Adventure</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Multiplayer'">#Multiplayer</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Platformer'">#Platformer</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Horror'">#Horror</span>
    <span class="tag" onclick="window.location.href='all_games.php?cat=Sports'">#Sports</span>
  </div>

  <!-- Google AdSense reklama -->
  <div class="my-4 text-center fade-in">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4883447881610919"
         crossorigin="anonymous"></script>
    <!-- Example AdSense block -->
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-4883447881610919"
         data-ad-slot="1234567890"
         data-ad-format="auto"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  </div>

  <!-- Featured Games Carousel -->
  <section class="mb-5 fade-in">
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
  <section class="mb-5 fade-in">
    <h2 class="section-title text-center">📰 News & Daily Tips</h2>
    <div class="row justify-content-center">
      <!-- NEWS carousel -->
      <div class="col-12 mb-4">
        <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
          <div class="carousel-inner">
            <?php $first = true; foreach ($newsChunks as $chunk): ?>
              <div class="carousel-item <?= $first ? 'active' : '' ?>">
                <div class="row">
                  <?php foreach ($chunk as $news): ?>
                    <div class="col-md-6">
                      <div class="news-block mb-2 d-flex flex-column h-100">
                        <div class="d-flex align-items-center mb-2">
                          <span class="badge bg-info"><?= htmlspecialchars(strtoupper($news['type'])) ?></span>
                          <h5 class="mb-0 ms-2"><?= htmlspecialchars($news['title']) ?></h5>
                        </div>
                        <p class="mb-2"><?= nl2br(htmlspecialchars(mb_strimwidth($news['content'], 0, 180, '...'))) ?></p>
                        <div class="news-meta">
                          <?= date('d.m.Y H:i', strtotime($news['created_at'])) ?>
                          <?php if ($news['author']): ?> | <span>by <?= htmlspecialchars($news['author']) ?></span><?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php $first = false; endforeach; ?>
          </div>
          <?php if (count($newsChunks) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- TIP carousel -->
      <div class="col-12">
        <div id="tipCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
          <div class="carousel-inner">
            <?php $first = true; foreach ($tipChunks as $chunk): ?>
              <div class="carousel-item <?= $first ? 'active' : '' ?>">
                <div class="row">
                  <?php foreach ($chunk as $tip): ?>
                    <div class="col-md-6">
                      <div class="news-block mb-2 d-flex flex-column h-100">
                        <div class="d-flex align-items-center mb-2">
                          <span class="badge bg-success"><?= htmlspecialchars(strtoupper($tip['type'])) ?></span>
                          <h5 class="mb-0 ms-2"><?= htmlspecialchars($tip['title']) ?></h5>
                        </div>
                        <p class="mb-2"><?= nl2br(htmlspecialchars(mb_strimwidth($tip['content'], 0, 180, '...'))) ?></p>
                        <div class="news-meta">
                          <?= date('d.m.Y H:i', strtotime($tip['created_at'])) ?>
                          <?php if ($tip['author']): ?> | <span>by <?= htmlspecialchars($tip['author']) ?></span><?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php $first = false; endforeach; ?>
          </div>
          <?php if (count($tipChunks) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#tipCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#tipCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Join Us -->
  <section class="join-us mt-5 p-5 text-center fade-in">
    <h2 class="mb-3">Join Our Gaming Community</h2>
    <p class="mb-4">Sign up to review games, save favorites, and get exclusive offers.</p>
    <a href="authentication.php" class="btn btn-outline-light btn-lg">Join Now</a>
  </section>
</div>

<?php include('layout/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll pro anchor odkazy
document.querySelectorAll('.smooth-scroll').forEach(el => {
  el.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href && href.startsWith('#')) {
      e.preventDefault();
      document.querySelector(href).scrollIntoView({behavior: 'smooth'});
    }
  });
});

// Fade-in animace při scrollu
function revealOnScroll() {
  document.querySelectorAll('.fade-in').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight - 60) {
      el.classList.add('visible');
    }
  });
}
window.addEventListener('scroll', revealOnScroll);
window.addEventListener('DOMContentLoaded', revealOnScroll);

// Swipe pro carousel na mobilu
document.querySelectorAll('.carousel').forEach(carousel => {
  let startX = 0;
  carousel.addEventListener('touchstart', e => {
    startX = e.touches[0].clientX;
  });
  carousel.addEventListener('touchend', e => {
    let endX = e.changedTouches[0].clientX;
    if (endX - startX > 50) {
      carousel.querySelector('.carousel-control-prev')?.click();
    } else if (startX - endX > 50) {
      carousel.querySelector('.carousel-control-next')?.click();
    }
  });
});
</script>
</body>
</html>
