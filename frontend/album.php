<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
$pagination = pagination_state($_GET, 18);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM categories');
$total = (int) ($totalRow['total'] ?? 0);
$albums = db_fetch_all(
    $conn,
    'SELECT category_artist, category_album, category_image, category_id FROM categories ORDER BY category_id ASC LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Songs by Album</h3></div></div></div></div></div>
<section class="oneMusic-buy-now-area has-fluid"><div class="container mt-5">
    <h4 class="text-center">Categories:</h4><div class="row mb-4"><div class="col-12 text-center"><a href="music.php" class="btn btn-danger mx-2 px-3">All</a><a href="artist.php" class="btn btn-warning mx-2 px-3">Artist</a><a href="genre.php" class="btn btn-success mx-2 px-3">Genre</a><a href="language.php" class="btn btn-primary mx-2 px-3">Language</a><a href="year.php" class="btn btn-secondary mx-2 px-3">Year</a></div></div>
    <div class="row mb-4"><div class="col-12 text-center"><form method="GET" action="search_album.php" class="d-flex justify-content-center"><div class="input-group" style="max-width: 400px;"><input type="search" name="q" class="form-control form-control-sm" placeholder="Search" aria-label="Search albums"><button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button></div></form></div></div><hr class="mb-4">
    <div class="row mt-5"><?php if ($albums !== []): foreach ($albums as $row): ?><div class="col-12 col-sm-6 col-md-4 col-lg-2"><div class="single-album-area wow fadeInUp" data-wow-delay="100ms"><a href="album_music.php?album_id=<?php echo (int) $row['category_id']; ?>"><div class="album-thumb"><img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['category_album']); ?>" style="max-width: 100%;"></div><div class="album-info"><h5><?php echo e($row['category_artist']); ?></h5><p><?php echo e($row['category_album']); ?></p></div></a></div></div><?php endforeach; else: ?><p>No albums found.</p><?php endif; ?></div>
    <?php echo pagination_links($pagination['page'], $totalPages); ?>
</div></section>
<?php include 'footer.php'; ?>
