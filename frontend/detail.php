<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
try {
    $audioId = request_positive_int($_GET, 'audio_id');
} catch (InvalidArgumentException) {
    http_response_code(404);
    $audioId = 0;
}
$audio = $audioId > 0 ? db_fetch_one(
    $conn,
    'SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_album, c.category_artist, c.category_year, c.category_language, c.category_image, c.category_genre
     FROM audio a INNER JOIN categories c ON a.category_id = c.category_id WHERE a.audio_id = ? LIMIT 1',
    'i',
    [$audioId]
) : null;
$pagination = pagination_state($_GET, 10);
$totalReviews = 0;
$reviews = [];
if ($audio !== null) {
    $countRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM review_audio WHERE audio_id = ?', 'i', [$audioId]);
    $totalReviews = (int) ($countRow['total'] ?? 0);
    $reviews = db_fetch_all(
        $conn,
        'SELECT r.review_id, r.review_text, r.review_rating, r.review_date, r.user_id AS reviewer_id, u.user_name, u.user_image
         FROM review_audio r INNER JOIN users u ON r.user_id = u.user_id
         WHERE r.audio_id = ? ORDER BY r.review_date DESC, r.review_id DESC LIMIT ? OFFSET ?',
        'iii',
        [$audioId, $pagination['per_page'], $pagination['offset']]
    );
}
$totalPages = pagination_total_pages($totalReviews, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Music</h3></div></div></div></div></div>
<section class="oneMusic-buy-now-area has-fluid section-padding-100"><div class="container">
<?php if ($audio !== null): ?>
    <div class="row mb-4"><div class="card mb-3 border-0" style="max-width: 100%"><div class="row g-0"><div class="col-md-4"><img src="../admin/uploads/images/<?php echo e($audio['category_image']); ?>" class="img-fluid" alt="Cover for <?php echo e($audio['category_album']); ?>"></div><div class="col-md-8"><div class="card-body" style="font-size: 1.2rem;"><h5 class="card-title" style="font-size: 1.5rem;"><?php echo e($audio['category_album']); ?></h5><p><strong>Artist:</strong> <?php echo e($audio['category_artist']); ?></p><p><strong>Genre:</strong> <?php echo e($audio['category_genre']); ?></p><p><strong>Language:</strong> <?php echo e($audio['category_language']); ?></p><p><strong>Year:</strong> <?php echo (int) $audio['category_year']; ?></p></div></div></div></div></div>
    <div class="row mb-4"><div class="table-responsive mt-4"><table class="table table-bordered" style="table-layout: fixed;"><thead class="bg-danger text-white"><tr><th style="width: 30%;">Song</th><th style="width: 50%;">Player</th><th style="width: 20%;">Action</th></tr></thead><tbody><tr><td><strong style="font-size: 1.2rem;"><?php echo e($audio['audio_title']); ?></strong></td><td><audio controls preload="none" style="width: 100%;"><source src="media.php?type=audio&amp;id=<?php echo (int) $audio['audio_id']; ?>" type="<?php echo e($audio['audio_mime_type']); ?>">Your browser does not support the audio element.</audio></td><td><a class="popup-video btn btn-danger mx-2" href="review_audio.php?audio_id=<?php echo (int) $audio['audio_id']; ?>">Review</a></td></tr></tbody></table></div></div>
<?php else: ?><p>Audio not found.</p><?php endif; ?>
<div class="d-flex justify-content-center"><a href="search_audio.php" class="btn oneMusic-btn">Search More Music</a></div></div></section>
<section class="oneMusic-buy-now-area has-fluid bg-light"><div class="container"><div class="comments-area"><h4 class="mb-4">Comments</h4>
<?php if ($reviews !== []): foreach ($reviews as $review): ?><div class="comment-list p-3 border rounded"><div class="single-comment"><div class="desc"><div class="user-info d-flex align-items-center mb-2"><img src="img/banner/<?php echo e($review['user_image']); ?>" alt="Profile image of <?php echo e($review['user_name']); ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;"><p class="mb-1"><?php echo e($review['user_name']); ?></p></div><p class="comment">Comments: <?php echo nl2br(e($review['review_text'])); ?></p><p class="comment">Rating: <?php echo (int) $review['review_rating']; ?>/5</p><p class="text-muted" style="font-size: 12px;"><?php echo e(date('F j, Y, g:i a', strtotime((string) $review['review_date']))); ?></p>
<?php if ((int) $current_user['user_id'] === (int) $review['reviewer_id']): ?><div class="reply-btn"><div class="play-icon d-flex justify-content-start"><a class="popup-video btn btn-primary" href="edit_review.php?review_id=<?php echo (int) $review['review_id']; ?>">Edit</a><form method="POST" action="delete_review.php" style="display: inline; margin-left: 5px;" onsubmit="return confirm('Are you sure you want to delete this review?')"><?php echo csrf_field(); ?><input type="hidden" name="review_id" value="<?php echo (int) $review['review_id']; ?>"><button type="submit" class="btn btn-danger ms-2">Delete</button></form></div></div><?php endif; ?>
</div></div></div><?php endforeach; else: ?><p>No reviews yet.</p><?php endif; ?>
<?php echo pagination_links($pagination['page'], $totalPages, ['audio_id' => $audioId]); ?>
</div></div></section>
<?php include 'footer.php'; ?>
