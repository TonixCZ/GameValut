<?php
require_once __DIR__ . '/includes/admin_logic.php';
require_once __DIR__ . '/includes/categories.php'; // přidej tento řádek
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
</head>
<body>
<?php include 'layout/header.php'; ?>
<div class="d-flex">
    <nav class="sidebar">
        <h3>Admin Panel</h3>
        <p class="mb-4">Logged in as:<br><strong><?=htmlspecialchars($user['first_name'])?></strong></p>
        <a href="#" class="nav-link active" data-tab="games">Add New Game</a>
        <a href="#" class="nav-link" data-tab="news">Manage News</a>
        <a href="#" class="nav-link" data-tab="users">Manage Users</a>
        <a href="#" class="nav-link" data-tab="manage_games">Manage Games</a>
        <a href="logout.php">Log Out</a>
    </nav>
    <main class="content container">
        <!-- Add Game -->
        <div class="tab-content">
            <div class="tab-pane active" id="games">
                <h1 class="mb-4">Add New Game</h1>
                <?php if ($successMsg): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <span class="bi bi-check-circle-fill" style="font-size:1.5em;"></span>
                        <span><?=htmlspecialchars($successMsg)?></span>
                    </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <span class="bi bi-exclamation-triangle-fill" style="font-size:1.5em;"></span>
                        <span><?=htmlspecialchars($errorMsg)?></span>
                    </div>
                <?php endif; ?>
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
        </div>
    </main>
</div>
<script>
    // Tab switching
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });
</script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
