<?php
// Spuštění session a připojení k databázi
session_start();
require_once __DIR__ . '/config.php';

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
  <title>Your Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap a vlastní styly -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/profile.css" />
  
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/footer.css" />
</head>
<body>

<?php include('layout/header.php'); ?>

<div class="container py-5">
  <!-- Úvodní sekce profilu -->
  <section class="hero text-center mb-5">
    <h1 class="display-5">Your Profile</h1>
    <p class="lead">Welcome to your account overview.</p>
  </section>

  <div class="row justify-content-center">
    <div class="col-lg-6">
      <!-- Shrnutí profilu uživatele -->
      <div class="profile-summary text-center p-4 mb-4">
        <img src="uploads/users/<?= htmlspecialchars($avatarFile) ?>" alt="Avatar" class="profile-avatar shadow">
        <h3 class="mt-3"><?= htmlspecialchars($user['nickname']) ?></h3>
        <p class="mb-1"><strong>Full name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p class="mb-1"><strong>Reviews:</strong> <?= $user['review_count'] ?></p>
        <a href="settings.php" class="btn btn-primary btn-edit">Settings</a>
      </div>
    </div>
  </div>

  <!-- Formulář pro úpravu profilu (skrytý, zobrazí se po kliknutí na Edit) -->
  <div class="edit-profile-form" id="editProfileForm">
    <h4 class="mb-3 text-center">Edit Profile</h4>
    <form action="includes/update_profile.php" method="post" enctype="multipart/form-data">
      <div class="mb-3 text-center">
        <img src="uploads/users/<?= htmlspecialchars($avatarFile) ?>" alt="Avatar" class="profile-avatar shadow mb-2">
        <input type="file" name="avatar" class="form-control" accept="image/*">
      </div>
      <div class="mb-3">
        <label class="form-label">Nickname</label>
        <input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
      </div>
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-success">Save Changes</button>
        <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php include('layout/footer.php'); ?>
<!-- Bootstrap JS a skript pro zobrazení/skrytí formuláře -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Ovládání zobrazení formuláře pro úpravu profilu
  const editBtn = document.getElementById('editProfileBtn');
  const editForm = document.getElementById('editProfileForm');
  const cancelBtn = document.getElementById('cancelEditBtn');
  editBtn?.addEventListener('click', () => {
    editForm.style.display = 'block';
    window.scrollTo({top: editForm.offsetTop - 40, behavior: 'smooth'});
  });
  cancelBtn?.addEventListener('click', () => {
    editForm.style.display = 'none';
  });
</script>
</body>
</html>
