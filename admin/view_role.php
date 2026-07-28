<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$roles = db_fetch_all($conn, 'SELECT role_id, role_type, created_at, updated_at FROM roles WHERE role_id IN (?, ?) ORDER BY role_id', 'ii', [ROLE_ADMIN, ROLE_USER]);
include 'header.php';
?>
<div class="container table-container"><h6 class="text-center">Fixed Application Roles</h6><p class="text-center text-muted">One Sound intentionally uses only Administrator and User roles. Authorization is implemented for these two roles only.</p><table class="table table-bordered"><thead class="bg-primary text-white"><tr><th>Role ID</th><th>Role Type</th><th>Created</th><th>Updated</th></tr></thead><tbody><?php foreach ($roles as $row): ?><tr><td><?php echo (int) $row['role_id']; ?></td><td><?php echo e($row['role_type']); ?></td><td><?php echo e($row['created_at']); ?></td><td><?php echo e($row['updated_at']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include 'footer.php'; ?>
