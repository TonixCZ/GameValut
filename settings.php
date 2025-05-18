<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nickname, first_name, last_name, email, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit();
}
$avatarFile = $user['avatar'] ?: 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/settings.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/footer.css" />
    <style>
        .invalid-feedback {
            display: block;
            color: #fff !important;
            font-size: 0.97em;
            margin-top: 0.2em;
        }
    </style>
</head>
<body>
<?php include('layout/header.php'); ?>

<div class="container py-5" style="max-width: 420px;">
    <h2 class="mb-4 text-center">Edit Profile</h2>
    <form id="settings-form" action="includes/update_profile.php" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="avatar-row text-center mb-3">
            <img src="uploads/users/<?= htmlspecialchars($avatarFile) ?>" alt="Avatar" class="profile-avatar">
            <input type="file" name="avatar" class="form-control mt-2 avatar-input" accept="image/*" style="max-width:320px;margin:0 auto;">
        </div>
        <div class="form-row">
            <label for="nickname">Nickname</label>
            <input type="text" name="nickname" id="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" class="form-control" required readonly>
            <button type="button" class="edit-btn" data-target="nickname" aria-label="Edit"><span class="edit-icon">&#9998;</span></button>
        </div>
        <div class="invalid-feedback" id="nickname-feedback"></div>

        <div class="form-row">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control" required readonly>
            <button type="button" class="edit-btn" data-target="first_name" aria-label="Edit"><span class="edit-icon">&#9998;</span></button>
        </div>

        <div class="form-row">
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control" required readonly>
            <button type="button" class="edit-btn" data-target="last_name" aria-label="Edit"><span class="edit-icon">&#9998;</span></button>
        </div>

        <div class="form-row">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required readonly>
            <button type="button" class="edit-btn" data-target="email" aria-label="Edit"><span class="edit-icon">&#9998;</span></button>
        </div>
        <div class="invalid-feedback" id="email-feedback"></div>

        <div class="form-row">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="New password" readonly>
            <button type="button" class="edit-btn" id="password-edit-btn" aria-label="Edit"><span class="edit-icon">&#9998;</span></button>
        </div>
        <div class="form-row">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Repeat password" readonly>
        </div>
        <div class="invalid-feedback" id="password-feedback"></div>

        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-success" id="save-btn" style="display:none;">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<?php include('layout/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Unlock fields on pencil click and show Save
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        if (target) {
            target.removeAttribute('readonly');
            target.focus();
            document.getElementById('save-btn').style.display = 'inline-block';
        }
    });
});

// AJAX check for nickname and email
['nickname', 'email'].forEach(function(field) {
    const input = document.getElementById(field);
    if (!input) return;
    input.addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length < (field === 'email' ? 5 : 3)) {
            input.classList.remove('is-invalid');
            document.getElementById(field + '-feedback').textContent = '';
            document.getElementById('save-btn').disabled = false;
            return;
        }
        fetch('includes/check_user.php?' + field + '=' + encodeURIComponent(val) + '&exclude=<?= $user_id ?>')
            .then(r => r.text())
            .then(msg => {
                if (msg) {
                    input.classList.add('is-invalid');
                    document.getElementById(field + '-feedback').textContent = msg;
                    document.getElementById('save-btn').disabled = true;
                } else {
                    input.classList.remove('is-invalid');
                    document.getElementById(field + '-feedback').textContent = '';
                    document.getElementById('save-btn').disabled = false;
                }
            });
    });
});

// Password match check
document.getElementById('settings-form').addEventListener('submit', function(e) {
    const pass = document.getElementById('password').value;
    const pass2 = document.getElementById('password_confirm').value;
    if (pass.length > 0 && pass !== pass2) {
        e.preventDefault();
        document.getElementById('password-feedback').textContent = "Passwords do not match.";
        document.getElementById('password_confirm').classList.add('is-invalid');
    } else {
        document.getElementById('password-feedback').textContent = "";
        document.getElementById('password_confirm').classList.remove('is-invalid');
    }
});

// Show Save when avatar changes
document.querySelector('input[type="file"][name="avatar"]').addEventListener('change', function() {
    document.getElementById('save-btn').style.display = 'inline-block';
});

// Unlock both password fields when clicking the pencil
document.getElementById('password-edit-btn').addEventListener('click', function() {
    document.getElementById('password').removeAttribute('readonly');
    document.getElementById('password_confirm').removeAttribute('readonly');
    document.getElementById('password').focus();
    document.getElementById('save-btn').style.display = 'inline-block';
});
</script>
</body>
</html>