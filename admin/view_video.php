<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

$pagination = pagination_state($_GET, 12);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM videos');
$total = (int) ($totalRow['total'] ?? 0);
$videos = db_fetch_all(
    $conn,
    'SELECT v.video_id, v.video_title, v.video_mime_type, c.category_artist
     FROM videos v
     INNER JOIN categories c ON v.category_id = c.category_id
     ORDER BY v.video_id DESC
     LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);

include 'header.php';
?>
<form method="GET" action="search_video.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group"><input type="search" name="q" class="form-control bg-light border-0 small" placeholder="Search for video..." aria-label="Search video"><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search fa-sm"></i></button></div></div>
</form>
<div class="container mt-5">
    <div class="row">
        <?php if ($videos !== []): foreach ($videos as $row): ?>
            <div class="col-md-4 mb-4"><div class="card d-flex flex-column">
                <div class="video-container" style="flex-grow: 1;"><video controls preload="metadata" class="card-img-top" style="width: 100%; height: 300px;"><source src="../frontend/media.php?type=video&amp;id=<?php echo (int) $row['video_id']; ?>" type="<?php echo e($row['video_mime_type']); ?>">Your browser does not support the video element.</video></div>
                <div class="card-body"><h6 class="card-title"><?php echo e($row['video_title']); ?></h6><p class="card-text"><?php echo e($row['category_artist']); ?></p><div class="mt-2">
                    <a href="edit_video.php?video_id=<?php echo (int) $row['video_id']; ?>" class="btn btn-primary">Edit</a>
                    <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video?')"><?php echo csrf_field(); ?><input type="hidden" name="entity" value="video"><input type="hidden" name="id" value="<?php echo (int) $row['video_id']; ?>"><button type="submit" class="btn btn-danger">Delete</button></form>
                </div></div>
            </div></div>
        <?php endforeach; else: ?><p class="col-12">No videos found.</p><?php endif; ?>
    </div>
    <?php echo pagination_links($pagination['page'], $totalPages); ?>
</div>
<?php include 'footer.php'; ?>
