<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
require_post_request();
try {
    verify_csrf();
    $reviewId = request_positive_int($_POST, 'review_id');
    $review = db_fetch_one($conn, 'SELECT audio_id FROM review_audio WHERE review_id = ? AND user_id = ? LIMIT 1', 'ii', [$reviewId, (int) $current_user['user_id']]);
    if ($review === null) {
        throw new RuntimeException('Review not found or you are not allowed to delete it.');
    }
    db_execute($conn, 'DELETE FROM review_audio WHERE review_id = ? AND user_id = ?', 'ii', [$reviewId, (int) $current_user['user_id']]);
    redirect_with_flash('detail.php?audio_id=' . (int) $review['audio_id'], 'Review deleted successfully.');
} catch (Throwable $exception) {
    error_log('[One Sound] Review delete error: ' . $exception->getMessage());
    redirect_with_flash('music.php', 'Unable to delete the review.', 'danger');
}
