<?php
require_once __DIR__ . '/includes/contact_logic.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Contact Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/footer.css" />
    <link rel="stylesheet" href="styles/contact.css" />
</head>
<body>
<?php include('layout/header.php'); ?>
<div class="container contact-container">
    <h2 class="mb-4 text-center">Write Us</h2>
    <?php if ($sent): ?>
        <div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off" class="contact-form">
            <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" id="message" class="form-control" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">Send Message</button>
        </form>
    <?php endif; ?>
</div>
<?php include('layout/footer.php'); ?>
</body>
</html>