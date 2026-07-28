<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

$error = '';
try {
    $searchQuery = input_search($_GET);
} catch (InvalidArgumentException $exception) {
    $searchQuery = '';
    $error = $exception->getMessage();
}
$pagination = pagination_state($_GET, 12);
$like = '%' . $searchQuery . '%';
$where = '(v.video_title LIKE ? OR c.category_artist LIKE ? OR c.category_album LIKE ? OR c.category_genre LIKE ? OR c.category_language LIKE ?)';
$types = 'sssss';
$params = [$like, $like, $like, $like, $like];
if ($searchQuery !== '' && ctype_digit($searchQuery)) {
    $where .= ' OR c.category_year = ?';
    $types .= 'i';
    $params[] = (int) $searchQuery;
}
$countRow = db_fetch_one($conn, "SELECT COUNT(*) AS total FROM videos v INNER JOIN categories c ON v.category_id = c.category_id WHERE $where", $types, $params);
$total = (int) ($countRow['total'] ?? 0);
$videoResults = db_fetch_all(
    $conn,
    "SELECT v.video_id, v.video_title, v.video_mime_type, c.category_artist, c.category_album, c.category_genre, c.category_language, c.category_year
     FROM videos v INNER JOIN categories c ON v.category_id = c.category_id
     WHERE $where ORDER BY v.video_id DESC LIMIT ? OFFSET ?",
    $types . 'ii',
    array_merge($params, [$pagination['per_page'], $pagination['offset']])
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<form method="GET" action="search_video.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group"><input type="search" name="q" value="<?php echo e($searchQuery); ?>" class="form-control bg-light border-0 small" placeholder="Search for video..." aria-label="Search video"><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search fa-sm"></i></button></div></div>
</form>
<div class="container mt-5"><h5>Search Results for “<?php echo e($searchQuery); ?>” — Video</h5>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?php echo e($error); ?></div><?php endif; ?>
    <div class="row mt-4">
    <?php if ($videoResults !== []): foreach ($videoResults as $row): ?><div class="col-md-4 mb-4"><div class="card d-flex flex-column">
        <div class="video-container" style="flex-grow: 1;"><video controls preload="metadata" class="card-img-top" style="width: 100%; height: 300px;"><source src="../frontend/media.php?type=video&amp;id=<?php echo (int) $row['video_id']; ?>" type="<?php echo e($row['video_mime_type']); ?>">Your browser does not support the video element.</video></div>
        <div class="card-body"><h6 class="card-title"><?php echo e($row['video_title']); ?></h6><p class="card-text"><?php echo e($row['category_artist']); ?><br><?php echo e($row['category_album']); ?></p><a href="edit_video.php?video_id=<?php echo (int) $row['video_id']; ?>" class="btn btn-primary">Edit</a></div>
    </div></div><?php endforeach; else: ?><p class="col-12">No video files found.</p><?php endif; ?>
    </div>
    <?php echo pagination_links($pagination['page'], $totalPages, ['q' => $searchQuery]); ?>
</div>
<?php include 'footer.php'; ?>
