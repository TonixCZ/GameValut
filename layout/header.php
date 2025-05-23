<?php
require_once 'src/session.php';
require_once 'includes/categories.php';

// Získání avataru a nicku uživatele (pokud je přihlášen)
$avatarFile = 'default.png';
$nickname = '';
if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT avatar, nickname FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $avatarFile = $userRow['avatar'] ?: 'default.png';
        $nickname = $userRow['nickname'] ?? '';
    } else {
        $avatarFile = 'default.png';
        $nickname = '';
    }
}
?>
<link rel="stylesheet" href="header.css">

<nav class="navbar">
  <div class="container d-flex align-items-center justify-content-between" style="position: relative;">
    <a href="/" class="navbar-brand d-flex align-items-center">
      <img src="/assets/images/WebLogo.png" alt="GameVault Logo" style="height:62px; margin-right:16px;">
      <span style="font-size:2.2rem; font-weight:700; color:#fff;">GameVault</span>
    </a>

    <ul class="nav gap-2">
      <li class="nav-item">
        <a href="/all_games" class="nav-link text-light fw-semibold">Browse All Games</a>
      </li>
      <li class="nav-item">
        <a href="/about" class="nav-link text-light fw-semibold">About Us</a>
      </li>
    </ul>

    <div class="auth-menu">
      <?php if ($isLoggedIn): ?>
        <div class="dropdown user-dropdown" style="position: relative;">
          <button
            type="button"
            class="dropdown-btn user-btn d-flex align-items-center"
            id="userDropdownBtn"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="userDropdownMenu"
          >
            <img src="/uploads/users/<?= htmlspecialchars($avatarFile) ?>"
                 alt="Avatar"
                 class="rounded-circle me-2"
                 style="width:32px; height:32px; object-fit:cover; border:1px solid #ccc;">
            <?= htmlspecialchars($nickname, ENT_QUOTES, 'UTF-8') ?> <span class="arrow">▼</span>
          </button>
          <ul class="dropdown-menu user-menu" id="userDropdownMenu" role="menu" aria-labelledby="userDropdownBtn" tabindex="-1">
            <li role="none"><a href="/profile" class="dropdown-item" role="menuitem">Profile</a></li>
            <?php if ($isAdmin): ?>
              <li role="none"><a href="/admin_panel" class="dropdown-item" role="menuitem">Admin Panel</a></li>
            <?php endif; ?>
            <li role="none"><hr class="dropdown-divider"></li>
            <li role="none"><a href="/logout" class="dropdown-item text-danger" role="menuitem">Logout</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="/authentication" class="login-link">Login / Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script src="/includes/dropdown.js" defer></script>
<script src="/includes/search_suggestions.js" defer></script>
