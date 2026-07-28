<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);

$pagination = pagination_state($_GET, 15);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM categories');
$total = (int) ($totalRow['total'] ?? 0);
$categories = db_fetch_all(
    $conn,
    'SELECT category_id, category_artist, category_album, category_genre, category_language, category_year
     FROM categories ORDER BY category_id DESC LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
$totalPages = pagination_total_pages($total, $pagination['per_page']);
include 'header.php';
?>
<div class="container mt-5">
    <div class="table-responsive"><table class="table table-bordered">
        <thead class="bg-primary text-white"><tr><th>ID</th><th>Artist</th><th>Album</th><th>Genre</th><th>Language</th><th>Year</th><th>Action</th></tr></thead>
        <tbody>
        <?php if ($categories !== []): foreach ($categories as $row): ?>
            <tr><td><?php echo (int) $row['category_id']; ?></td><td><?php echo e($row['category_artist']); ?></td><td><?php echo e($row['category_album']); ?></td><td><?php echo e($row['category_genre']); ?></td><td><?php echo e($row['category_language']); ?></td><td><?php echo (int) $row['category_year']; ?></td><td>
                <a href="edit_category.php?category_id=<?php echo (int) $row['category_id']; ?>" class="btn btn-primary">Edit</a>
                <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')"><?php echo csrf_field(); ?><input type="hidden" name="entity" value="category"><input type="hidden" name="id" value="<?php echo (int) $row['category_id']; ?>"><button type="submit" class="btn btn-danger">Delete</button></form>
            </td></tr>
        <?php endforeach; else: ?><tr><td colspan="7" class="text-center">No categories found</td></tr><?php endif; ?>
        </tbody>
    </table></div>
    <?php echo pagination_links($pagination['page'], $totalPages); ?>
</div>
<?php include 'footer.php'; ?>
