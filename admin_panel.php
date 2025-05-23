<?php
require_once __DIR__ . '/includes/admin_logic.php';
require_once __DIR__ . '/includes/categories.php';

// Kontrola přihlášení a role admin

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: authentication.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/admin_panel.css" />
    <link rel="stylesheet" href="styles/header.css" />
    <link rel="stylesheet" href="styles/footer.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
</head>
<body>
<?php include 'layout/header.php'; ?>
<!-- Hamburger pro mobil -->
<button class="sidebar-toggle d-lg-none" aria-label="Toggle sidebar" onclick="toggleSidebar()">
  <span class="bi bi-list" style="font-size:2em;"></span>
</button>
<div class="d-flex">
    <nav class="sidebar">
        <h3>Admin Panel</h3>
        <p class="mb-4">Logged in as:<br><strong><?=htmlspecialchars($user['first_name'])?></strong></p>
        <a href="#" class="nav-link active" data-tab="dashboard">Dashboard</a>
        <a href="#" class="nav-link" data-tab="games">Add New Game</a>
        <a href="#" class="nav-link" data-tab="news">Manage News</a>
        <a href="#" class="nav-link" data-tab="users">Manage Users</a>
        <a href="#" class="nav-link" data-tab="manage_games">Manage Games</a>
        <a href="#" class="nav-link" data-tab="manage_reviews">Manage Reviews</a>
        <a href="logout.php">Log Out</a>
    </nav>
    <main class="content container">
        <?php if (!empty($_SESSION['alert'])): ?>
            <div class="alert alert-<?= $_SESSION['alert']['type'] ?> d-flex align-items-center gap-2">
                <span class="bi <?= $_SESSION['alert']['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>" style="font-size:1.5em;"></span>
                <span><?= htmlspecialchars($_SESSION['alert']['msg']) ?></span>
            </div>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
        <div class="tab-content">
            <!-- Dashboard -->
            <div class="tab-pane active" id="dashboard">
                <h1 class="mb-4">📊 Dashboard</h1>
                <div class="row g-4 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $totalUsers ?></div>
                            <div class="stat-label">Users</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $unverifiedUsers ?></div>
                            <div class="stat-label">Unverified Users</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $totalGames ?></div>
                            <div class="stat-label">Games</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $totalReviews ?></div>
                            <div class="stat-label">Reviews</div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-7">
                        <div class="stat-card">
                            <h5 class="mb-3">Visits (last 7 days)</h5>
                            <canvas id="visitsChart" height="120"></canvas>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="stat-card h-100 d-flex flex-column justify-content-center align-items-center">
                            <div class="stat-label mb-2">Top Reviewer</div>
                            <div class="stat-value"><?= htmlspecialchars($topReviewer['nickname'] ?? '-') ?></div>
                            <div><?= $topReviewer['review_count'] ?? 0 ?> reviews</div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-4 col-md-2">
                        <div class="stat-card text-center">
                            <div class="stat-value"><?= $todayVisits ?></div>
                            <div class="stat-label">Today</div>
                        </div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="stat-card text-center">
                            <div class="stat-value"><?= $weekVisits ?></div>
                            <div class="stat-label">Last 7 Days</div>
                        </div>
                    </div>
                    <div class="col-4 col-md-2">
                        <div class="stat-card text-center">
                            <div class="stat-value"><?= $monthVisits ?></div>
                            <div class="stat-label">Last 30 Days</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add Game -->
            <div class="tab-pane" id="games">
                <h1 class="mb-4">Add New Game</h1>
                <form method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="add_game" value="1">
                    <div class="mb-3">
                        <label for="title" class="form-label">Game Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?=htmlspecialchars($title ?? '')?>" required minlength="2" maxlength="255"
                            placeholder="Enter game title" />
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Game Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required minlength="10"
                            placeholder="Enter detailed game description"><?=htmlspecialchars($description ?? '')?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($) *</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price"
                            value="<?=htmlspecialchars($price ?? '0.00')?>" required placeholder="Price in CZK" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Platforms *</label>
                        <div>
                            <?php foreach ($allPlatforms as $plat): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="platform[]" id="plat_<?=htmlspecialchars($plat)?>"
                                        value="<?=htmlspecialchars($plat)?>" <?=in_array($plat, $selectedPlatforms) ? 'checked' : ''?>>
                                    <label class="form-check-label" for="plat_<?=htmlspecialchars($plat)?>"><?=htmlspecialchars($plat)?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categories *</label>
                        <div>
                            <?php foreach ($categories as $cat): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="categories[]" id="cat_<?=htmlspecialchars($cat)?>"
                                        value="<?=htmlspecialchars($cat)?>" <?=in_array($cat, $selectedCategories) ? 'checked' : ''?>>
                                    <label class="form-check-label" for="cat_<?=htmlspecialchars($cat)?>"><?=htmlspecialchars($cat)?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Game Image *</label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*" required />
                    </div>
                    <button type="submit" class="btn btn-primary">Add Game</button>
                </form>
            </div>
            <!-- News -->
            <div class="tab-pane" id="news">
                <h1 class="mb-4">Manage News & Tips</h1>
                <?php if ($newsSuccess): ?>
                    <div class="alert alert-success"><?=htmlspecialchars($newsSuccess)?></div>
                <?php endif; ?>
                <?php if ($newsError): ?>
                    <div class="alert alert-danger"><?=htmlspecialchars($newsError)?></div>
                <?php endif; ?>
                <form method="POST" class="mb-5">
                    <input type="hidden" name="add_news" value="1">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="news_title" class="form-control" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="news_content" class="form-control" rows="4" required minlength="10"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select name="news_type" class="form-select" required>
                            <option value="news">News</option>
                            <option value="tip">Tip</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add News / Tip</button>
                </form>
                <h2 class="mb-3">Latest News & Tips</h2>
                <?php foreach ($newsList as $news): ?>
                    <div class="mb-4 p-3 rounded" style="background:#23272b;">
                        <h5><?=htmlspecialchars($news['title'])?></h5>
                        <div class="mb-2"><?=nl2br(htmlspecialchars($news['content']))?></div>
                        <div class="small text-secondary">
                            <?=date('d.m.Y H:i', strtotime($news['created_at']))?>
                            <?php if ($news['author']): ?> | by <?=htmlspecialchars($news['author'])?><?php endif; ?>
                        </div>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this news/tip?');">
                            <button type="submit" name="delete_news" value="<?=$news['id']?>" class="btn btn-sm btn-danger mt-2">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Users -->
            <div class="tab-pane" id="users">
                <h1 class="mb-4">Manage Users</h1>
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nickname</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Reviews</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nickname']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td><?= $u['review_count'] ?></td>
                            <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete user?');">
                                    <button type="submit" name="delete_user" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                <?php else: ?>
                                    <span class="text-secondary">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Manage Games -->
            <div class="tab-pane" id="manage_games">
                <h1 class="mb-4">Manage Games</h1>
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Platforms</th>
                            <th>Categories</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $games = $pdo->query("SELECT * FROM games ORDER BY created_at DESC")->fetchAll();
                    foreach ($games as $game): ?>
                        <tr>
                            <td><?= $game['id'] ?></td>
                            <td><?= htmlspecialchars($game['title']) ?></td>
                            <td><?= htmlspecialchars($game['platform']) ?></td>
                            <td><?= htmlspecialchars($game['categories']) ?></td>
                            <td>
                              <form method="post" style="display:inline-flex;gap:4px;">
                                <input type="number" step="0.01" min="0" name="edit_price" value="<?= htmlspecialchars($game['price']) ?>" style="width:80px;" class="form-control form-control-sm" />
                                <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                <button type="submit" name="save_price" class="btn btn-sm btn-success">Save</button>
                              </form>
                            </td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this game?');">
                                    <button type="submit" name="delete_game" value="<?= $game['id'] ?>" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Manage Reviews -->
            <div class="tab-pane" id="manage_reviews">
                <h1 class="mb-4">Manage Reviews</h1>
                <div class="table-responsive">
                    <table class="table table-dark table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Game</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?= $review['id'] ?></td>
                                <td><?= htmlspecialchars($review['title']) ?></td>
                                <td><?= htmlspecialchars($review['nickname']) ?></td>
                                <td><?= $review['rating'] ?></td>
                                <td style="max-width:200px;overflow:auto;"><?= htmlspecialchars($review['comment']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                                <td>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this review?');">
                                        <button type="submit" name="delete_review" value="<?= $review['id'] ?>" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php if (!empty($_SESSION['alert'])): ?>
    <div class="alert alert-<?= $_SESSION['alert']['type'] ?> d-flex align-items-center gap-2">
        <span class="bi <?= $_SESSION['alert']['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>" style="font-size:1.5em;"></span>
        <span><?= htmlspecialchars($_SESSION['alert']['msg']) ?></span>
    </div>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.visitsLabels = <?= json_encode(array_column($visitsPerDay, 'date')) ?>;
window.visitsData = <?= json_encode(array_column($visitsPerDay, 'count')) ?>;
</script>
<script src="js/admin_panel.js"></script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
