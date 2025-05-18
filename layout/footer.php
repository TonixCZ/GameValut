<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<link rel="stylesheet" href="/styles/footer.css">
<footer>
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="d-flex align-items-center" style="font-size:2.2rem; font-weight:700;">
          <img src="/assets/images/WebLogo.png" alt="GameVault Logo" style="height:62px;vertical-align:middle;margin-right:16px;">
          GameVault
        </h5>
        <p>
          The ultimate hangout for gamers. Rate, review, and discover epic games with the community.<br>
          No pay-to-win, just pure gaming vibes!
        </p>
      </div>
      <div class="col-md-4">
        <h6>Quick Links</h6>
        <ul class="list-unstyled">
          <li><a href="/">Home</a></li>
          <li><a href="/all_games.php">Browse All Games</a></li>
          <li><a href="/about.php">About Us</a></li>
          <li><a href="/contact.php">Contact Us</a></li>
          <?php if (!isset($_SESSION['user_id'])): ?>
            <li><a href="/authentication.php">Login</a></li>
            <li><a href="/authentication.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-md-4">
        <h6>Categories</h6>
        <ul class="list-unstyled">
          <?php
          require_once __DIR__ . '/../includes/categories.php';
          $footerCategories = ['Free to Play', 'Battle Royale', 'Shooter', 'RPG', 'Indie', 'Sports'];
          foreach ($footerCategories as $cat): ?>
            <li>
              <a href="/all_games.php?category=<?= urlencode($cat) ?>">
                <?= htmlspecialchars($cat) ?>
              </a>
            </li>
          <?php endforeach; ?>
          <li><a href="/all_games.php">Show all...</a></li>
        </ul>
      </div>
    </div>
    <div class="text-center mt-4 pt-3 border-top text-muted small">
      &copy; <?= date("Y") ?> GameVault. All rights reserved.
    </div>
  </div>
</footer>