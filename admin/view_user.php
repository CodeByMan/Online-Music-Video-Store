<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$pagination = pagination_state($_GET, 15);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM users');
$totalUsers = (int) ($totalRow['total'] ?? 0);
$users = db_fetch_all(
    $conn,
    'SELECT u.user_id, u.user_name, u.user_email, u.role_id, u.account_status, u.last_login_at, u.created_at, r.role_type
     FROM users u INNER JOIN roles r ON u.role_id = r.role_id ORDER BY u.user_id LIMIT ? OFFSET ?',
    'ii',
    [$pagination['per_page'], $pagination['offset']]
);
include 'header.php';
?>
<div class="container table-container"><h6 class="text-center">Total Users: <?php echo $totalUsers; ?></h6>
<?php if ($users !== []): ?><table class="table table-bordered"><thead class="bg-primary text-white"><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Last Login</th><th>Action</th></tr></thead><tbody>
<?php foreach ($users as $row): ?><tr><td><?php echo (int) $row['user_id']; ?></td><td><?php echo e($row['user_name']); ?></td><td><?php echo e($row['user_email']); ?></td><td><?php echo e($row['role_type']); ?></td><td><?php echo e(ucfirst($row['account_status'])); ?></td><td><?php echo e($row['created_at']); ?></td><td><?php echo e($row['last_login_at'] ?? 'Never'); ?></td><td><a href="edit_user.php?user_id=<?php echo (int) $row['user_id']; ?>" class="btn btn-primary">Edit</a><?php if ((int) $row['user_id'] !== (int) $current_user['user_id']): ?><form method="POST" action="delete.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this user?')"><?php echo csrf_field(); ?><input type="hidden" name="entity" value="user"><input type="hidden" name="id" value="<?php echo (int) $row['user_id']; ?>"><button type="submit" class="btn btn-danger">Delete</button></form><?php else: ?><span class="text-muted"> Current account</span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table><?php echo pagination_links($pagination['page'], pagination_total_pages($totalUsers, $pagination['per_page'])); ?><?php else: ?><p class="text-center">No users found</p><?php endif; ?></div>
<?php include 'footer.php'; ?>
