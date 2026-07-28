<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

$pagination = pagination_state($_GET, 15);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM audio');
$total = (int) ($totalRow['total'] ?? 0);
$musicFiles = db_fetch_all(
    $conn,
    'SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_artist
     FROM audio a
     INNER JOIN categories c ON a.category_id = c.category_id
     ORDER BY a.audio_id DESC
     LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);

include 'header.php';
?>
<form method="GET" action="search_audio.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group">
        <input type="search" name="q" class="form-control bg-light border-0 small" placeholder="Search for audio..." aria-label="Search audio">
        <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search fa-sm"></i></button></div>
    </div>
</form>
<div class="container mt-5">
    <?php if ($musicFiles !== []): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-primary text-white"><tr><th>Name</th><th>Artist</th><th>Audio</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($musicFiles as $row): ?>
                    <tr>
                        <td><?php echo e($row['audio_title']); ?></td>
                        <td><?php echo e($row['category_artist']); ?></td>
                        <td style="width: 300px;"><audio controls preload="none" class="w-100"><source src="../frontend/media.php?type=audio&amp;id=<?php echo (int) $row['audio_id']; ?>" type="<?php echo e($row['audio_mime_type']); ?>">Your browser does not support the audio element.</audio></td>
                        <td>
                            <a href="edit_music.php?audio_id=<?php echo (int) $row['audio_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this music file?')">
                                <?php echo csrf_field(); ?><input type="hidden" name="entity" value="audio"><input type="hidden" name="id" value="<?php echo (int) $row['audio_id']; ?>"><button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo pagination_links($pagination['page'], $totalPages); ?>
    <?php else: ?><p>No music files found.</p><?php endif; ?>
</div>
<?php include 'footer.php'; ?>
