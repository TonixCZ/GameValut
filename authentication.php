<?php
require_once __DIR__ . '/includes/auth_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Register | GameVault</title>
    <meta name="description" content="Log in or create a new account on GameVault. Join our gaming community to discover, review, and share the best games.">
    <meta name="keywords" content="GameVault, login, register, sign up, gaming community, account, discover games, game reviews">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/auth.css">
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
</head>
<body>
<style>
    body { background-color: #10151b; }
</style>
<?php include('layout/header.php'); ?>

<div class="container py-4">
    <?php if (!empty($message)): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> text-center fade show" id="message-alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['alert'])): ?>
        <div class="alert alert-<?= $_SESSION['alert']['type'] ?> mt-3" role="alert">
            <?= htmlspecialchars($_SESSION['alert']['msg']) ?>
        </div>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="wrapper">
        <div class="card-container" id="card-container">
            <div class="flip-inner">
                <!-- Login -->
                <div class="card login-card">
                    <h2>Login</h2>
                    <form method="POST">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <button type="submit" name="login" class="btn btn-primary">Log In</button>
                        <p class="mt-3">Don't have an account? <a href="#" id="show-register">Register</a></p>
                    </form>
                </div>

                <!-- Register -->
                <div class="card register-card">
                    <h2>Register</h2>
                    <form method="POST" id="register-form" autocomplete="off">
                        <input type="text" class="form-control" name="nickname" id="nickname" placeholder="Nickname" required>
                        <div id="nickname-check" class="text-danger small mb-2"></div>
                        <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                        <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                        <input type="email" class="form-control" name="email" id="reg-email" placeholder="Email" required>
                        <div id="email-check" class="text-danger small mb-2"></div>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                        <p class="mt-3">Already have an account? <a href="#" id="show-login">Log In</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/auth.js"></script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
