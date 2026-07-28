<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
try {
    $albumId = request_positive_int($_GET, 'album_id');
} catch (InvalidArgumentException) {
    http_response_code(404);
    $albumId = 0;
}
$album = $albumId > 0 ? db_fetch_one($conn, 'SELECT category_album, category_artist, category_year, category_language, category_image, category_genre FROM categories WHERE category_id = ? LIMIT 1', 'i', [$albumId]) : null;
$pagination = pagination_state($_GET, 15);
$total = 0;
$songs = [];
if ($album !== null) {
    $countRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM audio WHERE category_id = ?', 'i', [$albumId]);
    $total = (int) ($countRow['total'] ?? 0);
    $songs = db_fetch_all($conn, 'SELECT audio_id, audio_title, audio_mime_type FROM audio WHERE category_id = ? ORDER BY audio_title ASC LIMIT ? OFFSET ?', 'iii', [$albumId, $pagination['per_page'], $pagination['offset']]);
}
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Album Songs</h3></div></div></div></div></div>
<section class="oneMusic-buy-now-area has-fluid section-padding-100"><div class="container">
<?php if ($album !== null): ?>
    <div class="row mb-4"><div class="card mb-3 border-0" style="max-width: 100%"><div class="row g-0"><div class="col-md-4"><img src="../admin/uploads/images/<?php echo e($album['category_image']); ?>" class="img-fluid" alt="Cover for <?php echo e($album['category_album']); ?>"></div><div class="col-md-8"><div class="card-body" style="font-size: 1.2rem;"><h5 class="card-title" style="font-size: 1.5rem;"><?php echo e($album['category_album']); ?></h5><p><strong>Artist:</strong> <?php echo e($album['category_artist']); ?></p><p><strong>Genre:</strong> <?php echo e($album['category_genre']); ?></p><p><strong>Language:</strong> <?php echo e($album['category_language']); ?></p><p><strong>Year:</strong> <?php echo (int) $album['category_year']; ?></p></div></div></div></div></div>
    <div class="row mb-4"><div class="table-responsive mt-4"><?php if ($songs !== []): ?><table class="table table-bordered" style="table-layout: fixed;"><thead class="bg-danger text-white"><tr><th style="width: 40%;">Song</th><th style="width: 60%;">Player</th></tr></thead><tbody><?php foreach ($songs as $song): ?><tr><td><strong style="font-size: 1.2rem;"><?php echo e($song['audio_title']); ?></strong></td><td><audio controls preload="none" style="width: 100%;"><source src="media.php?type=audio&amp;id=<?php echo (int) $song['audio_id']; ?>" type="<?php echo e($song['audio_mime_type']); ?>">Your browser does not support the audio element.</audio></td></tr><?php endforeach; ?></tbody></table><?php else: ?><p>No songs available for this album.</p><?php endif; ?></div></div>
    <?php echo pagination_links($pagination['page'], $totalPages, ['album_id' => $albumId]); ?>
<?php else: ?><p>Album not found.</p><?php endif; ?>
<div class="d-flex justify-content-center"><a href="album.php" class="btn oneMusic-btn">Back to Albums</a></div></div></section>
<?php include 'footer.php'; ?>
