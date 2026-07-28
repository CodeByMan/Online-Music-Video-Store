<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);

try {
    $reviewId = request_positive_int($_GET, 'review_id');
    $review = db_fetch_one($conn, 'SELECT review_id, review_text, review_rating, audio_id FROM review_audio WHERE review_id = ? AND user_id = ? LIMIT 1', 'ii', [$reviewId, (int) $current_user['user_id']]);
    if ($review === null) {
        throw new RuntimeException('Review not found or you are not allowed to edit it.');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $reviewText = input_text($_POST, 'review_text', 1, 255, true);
        $rating = filter_var($_POST['review_rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($rating === false) {
            throw new InvalidArgumentException('Rating must be between 1 and 5.');
        }
        db_execute($conn, 'UPDATE review_audio SET review_text = ?, review_rating = ? WHERE review_id = ? AND user_id = ?', 'siii', [$reviewText, (int) $rating, $reviewId, (int) $current_user['user_id']]);
        redirect_with_flash('detail.php?audio_id=' . (int) $review['audio_id'], 'Review updated successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Review edit error: ' . $exception->getMessage());
    if (!isset($review) || $review === null) {
        redirect_with_flash('music.php', 'Review not found or you are not allowed to edit it.', 'danger');
    }
    $error_message = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the review. Please try again.';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="author" content="Muhammad Ali Nawaz"><title>One Sound - Music Unlimited</title><link rel="shortcut icon" type="image/x-icon" href="img/favicon.png"><link rel="stylesheet" href="css/bootstrap.min.css"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/styles.css"></head>
<body><div class="container mt-5"><h1>Edit Your Review</h1><?php if (isset($error_message)): ?><p class="text-danger" role="alert"><?php echo e($error_message); ?></p><?php endif; ?><form method="POST" action="edit_review.php?review_id=<?php echo (int) $reviewId; ?>"><?php echo csrf_field(); ?><label for="review_text">Review Text:</label><textarea id="review_text" name="review_text" class="form-control" rows="4" maxlength="255" placeholder="Write your review..." required><?php echo e($review['review_text']); ?></textarea><div class="form-group"><label for="review_rating">Rating (1 to 5):</label><input type="number" id="review_rating" class="form-control" name="review_rating" value="<?php echo (int) $review['review_rating']; ?>" min="1" max="5" required></div><button type="submit" class="btn btn-success">Update Review</button></form></div><footer class="py-4 text-center"><p>Developed by Muhammad Ali Nawaz</p></footer><script src="js/vendor/jquery-3.6.0.min.js"></script><script src="js/bootstrap.min.js"></script></body></html>
