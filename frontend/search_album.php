<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
$error = '';
try {
    $searchQuery = input_search($_GET);
} catch (InvalidArgumentException $exception) {
    $searchQuery = '';
    $error = $exception->getMessage();
}
$pagination = pagination_state($_GET, 18);
$condition = catalog_search_condition('a.audio_title', $searchQuery);
$countRow = db_fetch_one($conn, 'SELECT COUNT(DISTINCT c.category_id) AS total FROM categories c LEFT JOIN audio a ON a.category_id = c.category_id WHERE ' . $condition['where'], $condition['types'], $condition['params']);
$total = (int) ($countRow['total'] ?? 0);
$albums = db_fetch_all($conn, 'SELECT DISTINCT c.category_id, c.category_artist, c.category_album, c.category_image FROM categories c LEFT JOIN audio a ON a.category_id = c.category_id WHERE ' . $condition['where'] . ' ORDER BY c.category_id ASC LIMIT ? OFFSET ?', $condition['types'] . 'ii', array_merge($condition['params'], [$pagination['per_page'], $pagination['offset']]));
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Album</h3></div></div></div></div></div>
<section class="oneMusic-buy-now-area has-fluid section-padding-100"><div class="container"><h4 class="text-center">Search Albums By Categories:</h4><p class="text-center">(Title - Artist - Genre - Language - Year)</p><div class="row mb-4"><div class="col-12 text-center"><form method="GET" action="search_album.php" class="d-flex justify-content-center"><div class="input-group" style="max-width: 400px;"><input type="search" name="q" value="<?php echo e($searchQuery); ?>" class="form-control form-control-sm" placeholder="Search" aria-label="Search albums"><button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button></div></form></div></div><hr class="mb-4"><h5>Search Results for “<?php echo e($searchQuery); ?>”</h5>
<?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?php echo e($error); ?></div><?php endif; ?>
<div class="row mt-5"><?php if ($albums !== []): foreach ($albums as $row): ?><div class="col-12 col-sm-6 col-md-4 col-lg-2"><div class="single-album-area wow fadeInUp" data-wow-delay="100ms"><a href="album_music.php?album_id=<?php echo (int) $row['category_id']; ?>"><div class="album-thumb"><img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['category_album']); ?>" style="max-width: 100%;"></div><div class="album-info"><h5><?php echo e($row['category_artist']); ?></h5><p><?php echo e($row['category_album']); ?></p></div></a></div></div><?php endforeach; else: ?><p class="text-center">No albums found matching your search.</p><?php endif; ?></div>
<?php echo pagination_links($pagination['page'], $totalPages, ['q' => $searchQuery]); ?><div class="d-flex justify-content-center"><a href="album.php" class="btn oneMusic-btn">Back To Albums</a></div></div></section>
<?php include 'footer.php'; ?>
