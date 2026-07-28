<?php
include 'header.php';

$summary = db_fetch_one(
    $conn,
    "SELECT
        (SELECT COUNT(*) FROM roles) AS total_roles,
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM audio) AS total_music,
        (SELECT COUNT(*) FROM videos) AS total_videos,
        (SELECT COUNT(*) FROM messages WHERE message_status = 'pending') AS total_pending_messages"
) ?? [];

$totalRoles = (int) ($summary['total_roles'] ?? 0);
$totalUsers = (int) ($summary['total_users'] ?? 0);
$totalMusic = (int) ($summary['total_music'] ?? 0);
$totalVideos = (int) ($summary['total_videos'] ?? 0);
$totalMessages = (int) ($summary['total_pending_messages'] ?? 0);
?>

<h3 class="text-gray-600 text-center">Hi, <?php echo e($current_user['user_name']); ?></h3>

<div class="container mt-5">
    <h5 class="mb-4">Data Summary</h5>
    <table class="table table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                <th>Category</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Roles</td>
                <td><?php echo (int) $totalRoles; ?></td>
            </tr>
            <tr>
                <td>Total Users</td>
                <td><?php echo (int) $totalUsers; ?></td>
            </tr>
            <tr>
                <td>Total Music</td>
                <td><?php echo (int) $totalMusic; ?></td>
            </tr>
            <tr>
                <td>Total Videos</td>
                <td><?php echo (int) $totalVideos; ?></td>
            </tr>
            <tr>
                <td>Pending Messages</td>
                <td><?php echo (int) $totalMessages; ?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
