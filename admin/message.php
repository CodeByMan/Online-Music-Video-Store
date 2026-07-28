<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$status = $_GET['status'] ?? 'pending';
if (!is_string($status) || !in_array($status, ['pending', 'seen'], true)) {
    $status = 'pending';
}
$pagination = pagination_state($_GET, 15);
$totalRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM messages WHERE message_status = ?', 's', [$status]);
$total = (int) ($totalRow['total'] ?? 0);
$messages = db_fetch_all(
    $conn,
    'SELECT m.message_id, m.message_name, m.message_email, m.message_subject, m.message_detail, m.message_status, m.created_at, m.read_at, u.user_name AS reader_name
     FROM messages m LEFT JOIN users u ON m.read_by = u.user_id
     WHERE m.message_status = ? ORDER BY m.created_at DESC LIMIT ? OFFSET ?',
    'sii',
    [$status, $pagination['per_page'], $pagination['offset']]
);
include 'header.php';
?>
<div class="container table-container"><h5 class="text-center"><?php echo $status === 'pending' ? 'Unread Messages' : 'Read Messages'; ?></h5>
<div class="text-center mb-3"><a class="btn btn-sm <?php echo $status === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="message.php?status=pending">Unread</a> <a class="btn btn-sm <?php echo $status === 'seen' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="message.php?status=seen">Read</a></div>
<?php if ($messages !== []): ?><table class="table table-bordered"><thead class="bg-primary text-white"><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Received</th><?php if ($status === 'seen'): ?><th>Read</th><?php endif; ?><th>Action</th></tr></thead><tbody>
<?php foreach ($messages as $row): ?><tr><td><?php echo e($row['message_name']); ?></td><td><?php echo e($row['message_email']); ?></td><td><?php echo e($row['message_subject']); ?></td><td><?php echo nl2br(e($row['message_detail'])); ?></td><td><?php echo e($row['created_at']); ?></td><?php if ($status === 'seen'): ?><td><?php echo e(($row['read_at'] ?? 'Unknown') . ($row['reader_name'] ? ' by ' . $row['reader_name'] : '')); ?></td><?php endif; ?><td><?php if ($status === 'pending'): ?><form method="POST" action="update_message.php" style="display:inline"><?php echo csrf_field(); ?><input type="hidden" name="message_id" value="<?php echo (int) $row['message_id']; ?>"><input type="hidden" name="message_status" value="seen"><button type="submit" class="btn btn-primary">Read</button></form><?php else: ?><form method="POST" action="delete.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this message?')"><?php echo csrf_field(); ?><input type="hidden" name="entity" value="message"><input type="hidden" name="id" value="<?php echo (int) $row['message_id']; ?>"><button type="submit" class="btn btn-danger">Delete</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table><?php echo pagination_links($pagination['page'], pagination_total_pages($total, $pagination['per_page']), ['status' => $status]); ?><?php else: ?><p class="text-center">No <?php echo e($status); ?> messages</p><?php endif; ?></div>
<?php include 'footer.php'; ?>
