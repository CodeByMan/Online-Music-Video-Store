<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
$pagination = pagination_state($_GET, 18);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM videos');
$total = (int) ($totalRow['total'] ?? 0);
$videos = db_fetch_all($conn, 'SELECT v.video_id, v.video_title, c.category_artist, c.category_image FROM videos v INNER JOIN categories c ON v.category_id = c.category_id ORDER BY v.video_title ASC LIMIT ? OFFSET ?', 'ii', [$pagination['per_page'], $pagination['offset']]);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Videos</h3></div></div></div></div></div>
<section class="oneMusic-buy-now-area has-fluid mt-5"><div class="container mt-5"><h4 class="text-center">Search Videos By Categories:</h4><p class="text-center">(Artist - Genre - Language - Year)</p><div class="col-12 text-center"><form method="GET" action="search_video.php" class="d-flex justify-content-center"><div class="input-group" style="max-width: 400px;"><input type="search" name="q" class="form-control form-control-sm" placeholder="Search" aria-label="Search videos"><button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button></div></form></div>
<div class="row mt-5"><?php if ($videos !== []): foreach ($videos as $row): ?><div class="col-12 col-sm-6 col-md-4 col-lg-2"><div class="single-album-area wow fadeInUp" data-wow-delay="100ms"><div class="album-thumb"><img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['video_title']); ?>" style="max-width: 100%;"><div class="play-icon"><a class="popup-video" href="media.php?type=video&amp;id=<?php echo (int) $row['video_id']; ?>" aria-label="Play <?php echo e($row['video_title']); ?>"><span class="fa fa-play"></span></a></div></div><div class="album-info"><h5><?php echo e($row['video_title']); ?></h5><p><?php echo e($row['category_artist']); ?></p></div></div></div><?php endforeach; else: ?><p>No video files found.</p><?php endif; ?></div>
<?php echo pagination_links($pagination['page'], $totalPages); ?>
</div></section>
<?php include 'footer.php'; ?>
