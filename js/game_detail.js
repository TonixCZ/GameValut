document.addEventListener('DOMContentLoaded', function() {
    const GAME_ID = window.GAME_ID;

    // Sort reviews
    const sortSelect = document.getElementById('sort-reviews');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sort = this.value;
            fetch(`includes/ajax_reviews.php?id=${GAME_ID}&sort=${encodeURIComponent(sort)}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('reviews-container').innerHTML = html;
                    rebindReviewEvents();
                });
        });
    }

    // Alert auto-hide
    setTimeout(() => {
        const alert = document.getElementById('alert-message');
        if(alert) alert.classList.remove('show');
    }, 3500);

    // Scroll & blink to edit form if editing
    if (window.EDIT_REVIEW) {
        const form = document.getElementById('review-form-container');
        if(form) {
            form.scrollIntoView({behavior: "smooth", block: "center"});
            form.classList.add('blink');
            setTimeout(() => form.classList.remove('blink'), 1500);
        }
    }

    // Show/hide new review form
    const writeBtn = document.getElementById('write-review-btn');
    if (writeBtn) {
        writeBtn.addEventListener('click', () => {
            const formContainer = document.getElementById('review-form-container');
            if (formContainer.style.display === 'none' || !formContainer.style.display) {
                formContainer.style.display = 'block';
            } else {
                formContainer.style.display = 'none';
            }
        });
    }

    document.querySelectorAll('.star-rating').forEach(function(starRating) {
        const stars = starRating.querySelectorAll('.star');
        const inputId = starRating.dataset.input;
        const input = document.getElementById(inputId);
        let currentValue = parseFloat(starRating.dataset.value) || 0;

        function setStars(value) {
            stars.forEach((star, i) => {
                star.classList.remove('filled', 'half');
                if (value >= i + 1) {
                    star.classList.add('filled');
                } else if (value > i && value < i + 1) {
                    star.classList.add('half');
                }
            });
        }

        setStars(currentValue);

        if (input) input.value = currentValue;

        stars.forEach((star, i) => {
            star.addEventListener('mousemove', function(e) {
                const rect = star.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                let value = i + (percent >= 0.5 ? 1 : 0.5);
                setStars(value);
            });
            star.addEventListener('mouseleave', function() {
                setStars(currentValue);
            });
            star.addEventListener('click', function(e) {
                const rect = star.getBoundingClientRect();
                let percent = (e.clientX - rect.left) / rect.width;
                currentValue = i + (percent >= 0.5 ? 1 : 0.5);
                setStars(currentValue);
                if (input) input.value = currentValue;
            });
        });
    });

    document.querySelectorAll('form.review-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const textarea = form.querySelector('textarea[name="comment"]');
            if (textarea && textarea.value.trim().length < 30) {
                alert("Your review must be at least 30 characters long.");
                textarea.focus();
                e.preventDefault();
            }
        });
    });

    // Funkce pro znovu-navěšení eventů na nové prvky po AJAXu
    function rebindReviewEvents() {
        document.querySelectorAll('.like-form').forEach(form => {
            form.addEventListener('click', async function(e) {
                e.preventDefault();
                const btn = form.querySelector('.like-btn');
                if (btn.disabled) return;
                const reviewId = form.dataset.review;
                const heart = form.querySelector('.review-heart');
                const countSpan = form.querySelector('.like-count');
                btn.disabled = true;
                const res = await fetch('includes/like_review.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'review_id=' + encodeURIComponent(reviewId)
                });
                const data = await res.json();
                if (data.success) {
                    countSpan.textContent = data.count;
                    if (data.liked) {
                        heart.classList.add('liked');
                    } else {
                        heart.classList.remove('liked');
                    }
                }
                btn.disabled = false;
            });
        });

        document.querySelectorAll('.toggle-comments').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = btn.dataset.review;
                const commentsDiv = document.getElementById('comments-' + reviewId);
                const arrow = btn.querySelector('.comment-arrow');
                if (commentsDiv.style.display === 'none' || !commentsDiv.style.display) {
                    commentsDiv.style.display = 'block';
                    arrow.innerHTML = '&#9660;';
                } else {
                    commentsDiv.style.display = 'none';
                    arrow.innerHTML = '&#9654;';
                }
            });
        });

        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = btn.dataset.review;
                const replyForm = document.getElementById('reply-form-' + reviewId);
                if (replyForm.style.display === 'none' || !replyForm.style.display) {
                    replyForm.style.display = 'block';
                    btn.classList.add('active');
                } else {
                    replyForm.style.display = 'none';
                    btn.classList.remove('active');
                }
            });
        });

        document.querySelectorAll('.edit-comment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = btn.dataset.comment;
                document.getElementById('edit-comment-form-' + id).style.display = 'block';
                btn.closest('.review-comment').style.display = 'none';
            });
        });
        document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = btn.closest('.edit-comment-form');
                form.style.display = 'none';
                const id = form.id.replace('edit-comment-form-', '');
                document.querySelector('.review-comment[data-comment-id="' + id + '"]').style.display = 'block';
            });
        });
    }

    // Po načtení stránky navěs eventy poprvé
    rebindReviewEvents();
    window.rebindReviewEvents = rebindReviewEvents;
});