<?php
// Spuštění session a připojení k databázi
session_start();
require_once __DIR__ . '/config.php';

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    header("Location: authentication.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Načtení údajů o uživateli včetně počtu recenzí
$stmt = $pdo->prepare("SELECT nickname, first_name, last_name, email, avatar, (SELECT COUNT(*) FROM reviews WHERE user_id = ?) AS review_count FROM users WHERE id = ?");
$stmt->execute([$user_id, $user_id]);
$user = $stmt->fetch();

// Kontrola, zda byl uživatel nalezen
if (!$user) {
    echo "Uživatel nenalezen.";
    exit();
}
$avatarFile = $user['avatar'] ?: 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Profile | GameVault</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/profile_modern.css" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/footer.css" />
  <link rel="icon" type="image/png" href="/assets/images/logo.png">
</head>
<body>

<?php include('layout/header.php'); ?>

<div class="container py-5">
  <div class="profile-modern bg-dark rounded-4 shadow-lg p-4 mx-auto" style="max-width: 540px;">
    <div class="row g-0 align-items-center">
      <div class="col-12 col-md-4 text-center mb-3 mb-md-0">
        <img src="uploads/users/<?= htmlspecialchars($avatarFile) ?>" alt="Avatar" class="profile-avatar-modern shadow">
      </div>
      <div class="col-12 col-md-8">
        <h2 class="mb-2"><?= htmlspecialchars($user['nickname']) ?></h2>
        <ul class="list-unstyled mb-3">
          <li><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></li>
          <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
          <li><strong>Reviews:</strong> <?= $user['review_count'] ?></li>
        </ul>
        <div class="d-flex gap-2">
          <a href="settings.php" class="btn btn-primary flex-fill">Edit Profile</a>
          <a href="logout.php" class="btn btn-outline-light flex-fill">Log Out</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include('layout/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
