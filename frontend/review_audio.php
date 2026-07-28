<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);

try {
    $audioId = request_positive_int($_GET, 'audio_id');
    $audio = db_fetch_one($conn, 'SELECT audio_id, audio_title FROM audio WHERE audio_id = ? LIMIT 1', 'i', [$audioId]);
    if ($audio === null) {
        throw new RuntimeException('Audio not found.');
    }

    $existing = db_fetch_one($conn, 'SELECT review_id FROM review_audio WHERE audio_id = ? AND user_id = ? LIMIT 1', 'ii', [$audioId, (int) $current_user['user_id']]);
    if ($existing !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        redirect_with_flash('edit_review.php?review_id=' . (int) $existing['review_id'], 'You already reviewed this track. Update your existing review instead.', 'info');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        enforce_action_rate_limit($conn, 'review_create', 'user:' . (int) $current_user['user_id'], 5, 600, 900);
        if ($existing !== null) {
            throw new RuntimeException('You have already reviewed this track.');
        }
        $reviewText = input_text($_POST, 'review_text', 1, 255, true);
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($rating === false) {
            throw new InvalidArgumentException('Rating must be between 1 and 5.');
        }
        try {
            db_execute($conn, 'INSERT INTO review_audio (review_text, review_rating, audio_id, user_id) VALUES (?, ?, ?, ?)', 'siii', [$reviewText, (int) $rating, $audioId, (int) $current_user['user_id']]);
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                throw new RuntimeException('You have already reviewed this track.', 0, $exception);
            }
            throw $exception;
        }
        redirect_with_flash('detail.php?audio_id=' . $audioId, 'Review submitted successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Review submission error: ' . $exception->getMessage());
    $destination = isset($audioId) ? 'detail.php?audio_id=' . $audioId : 'music.php';
    redirect_with_flash($destination, $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to submit the review. Please try again.', 'danger');
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head><meta charset="utf-8"><meta http-equiv="x-ua-compatible" content="ie=edge"><title>One Sound - Music Unlimited</title><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="author" content="Muhammad Ali Nawaz"><link rel="shortcut icon" type="image/x-icon" href="img/favicon.png"><link rel="stylesheet" href="css/bootstrap.min.css"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/styles.css"></head>
<body><div class="container mt-5"><h2>Review Audio: <?php echo e($audio['audio_title']); ?></h2><p>Add New Review</p>
<form method="POST" action="review_audio.php?audio_id=<?php echo (int) $audioId; ?>"><?php echo csrf_field(); ?><div class="form-group"><label for="review_text">Your Review</label><textarea id="review_text" name="review_text" class="form-control" rows="4" maxlength="255" required></textarea></div><div class="form-group"><label for="rating">Rating (1 to 5)</label><input type="number" id="rating" name="rating" class="form-control" min="1" max="5" required></div><button type="submit" class="btn btn-success">Submit Review</button></form>
</div><footer class="py-4 text-center"><p>Developed by Muhammad Ali Nawaz</p></footer><script src="js/vendor/jquery-3.6.0.min.js"></script><script src="js/bootstrap.min.js"></script></body></html>
