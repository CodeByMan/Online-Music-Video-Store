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
$pagination = pagination_state($_GET, 20);
$condition = catalog_search_condition('a.audio_title', $searchQuery);
$countRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM audio a INNER JOIN categories c ON a.category_id = c.category_id WHERE ' . $condition['where'], $condition['types'], $condition['params']);
$total = (int) ($countRow['total'] ?? 0);
$audioResults = db_fetch_all(
    $conn,
    'SELECT a.audio_id, a.audio_title, c.category_artist, c.category_album, c.category_genre, c.category_year
     FROM audio a INNER JOIN categories c ON a.category_id = c.category_id
     WHERE ' . $condition['where'] . ' ORDER BY a.audio_title ASC LIMIT ? OFFSET ?',
    $condition['types'] . 'ii',
    array_merge($condition['params'], [$pagination['per_page'], $pagination['offset']])
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Songs</h3></div></div></div></div></div>
<section><div class="container mt-5"><h4 class="text-center">Search Music By Categories:</h4><p class="text-center">(Title - Artist - Album - Genre - Language - Year)</p>
<div class="col-12 text-center"><form method="GET" action="search_audio.php" class="d-flex justify-content-center"><div class="input-group" style="max-width: 400px;"><input type="search" name="q" value="<?php echo e($searchQuery); ?>" class="form-control form-control-sm" placeholder="Search" aria-label="Search music"><button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button></div></form></div><hr class="mb-4"><h5>Search Results for “<?php echo e($searchQuery); ?>” — Audio</h5>
<?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?php echo e($error); ?></div><?php endif; ?>
<?php if ($audioResults !== []): ?><div class="table-responsive"><table class="table table-bordered" style="table-layout: fixed;"><thead class="bg-danger text-white"><tr><th>Song</th><th>Artist</th><th>Album</th><th>Genre</th><th>Year</th></tr></thead><tbody><?php foreach ($audioResults as $row): ?><tr><td><a class="text-danger" href="detail.php?audio_id=<?php echo (int) $row['audio_id']; ?>"><?php echo e($row['audio_title']); ?> (Click Here)</a></td><td><strong><?php echo e($row['category_artist']); ?></strong></td><td><strong><?php echo e($row['category_album']); ?></strong></td><td><strong><?php echo e($row['category_genre']); ?></strong></td><td><strong><?php echo (int) $row['category_year']; ?></strong></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><p>No audio files found.</p><?php endif; ?>
<?php echo pagination_links($pagination['page'], $totalPages, ['q' => $searchQuery]); ?><div class="d-flex justify-content-center"><a href="music.php" class="btn oneMusic-btn m-5">Back To All Music</a></div></div></section>
<?php include 'footer.php'; ?>
