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
$pagination = pagination_state($_GET, 15);
$like = '%' . $searchQuery . '%';
$where = '(a.audio_title LIKE ? OR c.category_artist LIKE ? OR c.category_album LIKE ? OR c.category_genre LIKE ? OR c.category_language LIKE ?)';
$types = 'sssss';
$params = [$like, $like, $like, $like, $like];
if ($searchQuery !== '' && ctype_digit($searchQuery)) {
    $where .= ' OR c.category_year = ?';
    $types .= 'i';
    $params[] = (int) $searchQuery;
}
$countRow = db_fetch_one($conn, "SELECT COUNT(*) AS total FROM audio a INNER JOIN categories c ON a.category_id = c.category_id WHERE $where", $types, $params);
$total = (int) ($countRow['total'] ?? 0);
$dataParams = array_merge($params, [$pagination['per_page'], $pagination['offset']]);
$audioResults = db_fetch_all(
    $conn,
    "SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_artist, c.category_album, c.category_genre, c.category_language, c.category_year, c.category_image
     FROM audio a INNER JOIN categories c ON a.category_id = c.category_id
     WHERE $where ORDER BY a.audio_id DESC LIMIT ? OFFSET ?",
    $types . 'ii',
    $dataParams
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<form method="GET" action="search_audio.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group"><input type="search" name="q" value="<?php echo e($searchQuery); ?>" class="form-control bg-light border-0 small" placeholder="Search for audio..." aria-label="Search audio"><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search fa-sm"></i></button></div></div>
</form>
<div class="container mt-5">
    <h5>Search Results for “<?php echo e($searchQuery); ?>” — Audio</h5>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($audioResults !== []): ?><div class="table-responsive"><table class="table table-bordered">
        <thead class="bg-primary text-white"><tr><th>Poster</th><th>Song</th><th>Artist</th><th>Album</th><th>Genre</th><th>Language</th><th>Year</th><th>Audio Preview</th></tr></thead>
        <tbody><?php foreach ($audioResults as $row): ?><tr>
            <td><img src="uploads/images/<?php echo e($row['category_image']); ?>" class="img-thumbnail" alt="Cover for <?php echo e($row['category_album']); ?>" style="max-width: 100px; height: auto;"></td>
            <td><?php echo e($row['audio_title']); ?></td><td><?php echo e($row['category_artist']); ?></td><td><?php echo e($row['category_album']); ?></td><td><?php echo e($row['category_genre']); ?></td><td><?php echo e($row['category_language']); ?></td><td><?php echo (int) $row['category_year']; ?></td>
            <td><audio controls preload="none"><source src="../frontend/media.php?type=audio&amp;id=<?php echo (int) $row['audio_id']; ?>" type="<?php echo e($row['audio_mime_type']); ?>">Your browser does not support the audio element.</audio></td>
        </tr><?php endforeach; ?></tbody>
    </table></div><?php echo pagination_links($pagination['page'], $totalPages, ['q' => $searchQuery]); ?><?php else: ?><p>No audio files found.</p><?php endif; ?>
</div>
<?php include 'footer.php'; ?>
