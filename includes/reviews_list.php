<?php
// filepath: c:\Users\tonic\OneDrive\Desktop\Game-Valut\includes\reviews_list.php
?>
<?php if ($reviews): ?>
    <?php foreach ($reviews as $review): ?>
        <div class="review-card <?= (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']) ? 'my-review' : '' ?>" data-review-id="<?= $review['id'] ?>">
            <div class="d-flex justify-content-between align-items-center">
                <span class="username">@<?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></span>
                <span class="text-warning"><?= str_repeat("★", (int)$review['rating']) ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>

            <?php
            $likeCount = $pdo->query("SELECT COUNT(*) FROM review_likes WHERE review_id = {$review['id']}")->fetchColumn();
            $userLiked = false;
            if (isset($_SESSION['user_id'])) {
                $stmt = $pdo->prepare("SELECT 1 FROM review_likes WHERE review_id = ? AND user_id = ?");
                $stmt->execute([$review['id'], $_SESSION['user_id']]);
                $userLiked = $stmt->fetchColumn() ? true : false;
            }
            ?>
            <form class="like-form" data-review="<?= $review['id'] ?>" style="display:inline;">
                <button type="button" class="btn btn-link p-0 like-btn" style="vertical-align:middle;" <?= isset($_SESSION['user_id']) ? '' : 'disabled' ?> title="<?= $userLiked ? 'Unlike' : 'Like' ?>">
                    <span class="review-heart<?= $userLiked ? ' liked' : '' ?>">&#10084;</span>
                    <span class="like-count"><?= $likeCount ?></span>
                </button>
            </form>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                <div class="mt-2">
                    <form method="get" action="" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $game_id ?>">
                        <input type="hidden" name="edit_review" value="<?= $review['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary">Edit</button>
                    </form>
                    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <input type="hidden" name="game_id" value="<?= $game_id ?>">
                        <button type="submit" name="delete_review" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php
                $comments = $commentsByReview[$review['id']] ?? [];
                $commentCount = count($comments);
            ?>
            <div class="d-flex align-items-center gap-2 mt-2 ms-3">
                <?php if ($commentCount > 0): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-comments" data-review="<?= $review['id'] ?>">
                        <span class="me-1"><?= $commentCount ?></span>
                        <span class="comment-arrow">&#9654;</span>
                        <span class="ms-1">Comments</span>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary reply-btn" data-review="<?= $review['id'] ?>">
                    Reply
                </button>
            </div>
            <div class="review-comments mt-2 ms-3" id="comments-<?= $review['id'] ?>" style="display:none;">
                <?php foreach ($comments as $comment): ?>
                    <div class="review-comment mb-2 p-2 rounded d-flex flex-column" style="background:#23243a; border-left:3px solid #3db4f2;" data-comment-id="<?= $comment['id'] ?>">
                        <div class="d-flex align-items-center mb-1">
                            <span class="fw-bold text-primary me-2">@<?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></span>
                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></small>
                        </div>
                        <div class="ps-2 mb-1"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                        <?php if (
                            isset($_SESSION['user_id']) &&
                            ($_SESSION['user_id'] == $comment['user_id'] || ($_SESSION['role'] ?? '') === 'admin')
                        ): ?>
                            <div>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="return confirm('Delete this comment?')">Delete</button>
                                </form>
                                <button type="button" class="btn btn-link btn-sm text-warning p-0 ms-1 edit-comment-btn" data-comment="<?= $comment['id'] ?>">Edit</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="edit-comment-form mt-1" id="edit-comment-form-<?= $comment['id'] ?>" style="display:none;">
                        <input type="hidden" name="edit_comment_id" value="<?= $comment['id'] ?>">
                        <div class="input-group input-group-sm">
                            <input type="text" name="edit_comment_text" class="form-control" value="<?= htmlspecialchars($comment['comment']) ?>" required>
                            <button type="submit" class="btn btn-outline-success btn-sm">Save</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm cancel-edit-btn">Cancel</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
            <div class="reply-form-container ms-3 mt-2" id="reply-form-<?= $review['id'] ?>" style="display:none;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <input type="hidden" name="game_id" value="<?= $game_id ?>">
                        <div class="input-group input-group-sm">
                            <input type="text" name="comment" class="form-control" placeholder="Reply..." required>
                            <button type="submit" name="submit_comment" class="btn btn-outline-primary btn-sm">Send</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-muted">You must be logged in to reply.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">No reviews yet.</p>
<?php endif; ?>