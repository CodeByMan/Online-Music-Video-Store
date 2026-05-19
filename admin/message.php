<?php 
include('header.php');

$sql = "SELECT * FROM messages";
$result = mysqli_query($conn, $sql);

$sql_pending = "SELECT * FROM messages WHERE message_status = 'pending'";
$result_pending = mysqli_query($conn, $sql_pending);

$sql_seen = "SELECT * FROM messages WHERE message_status = 'seen'";
$result_seen = mysqli_query($conn, $sql_seen);

?>

<!-- Pending Messages Section -->
<div class="container table-container">
    <h5 class="text-center">Unread Messages
    </h5>
    <?php if (mysqli_num_rows($result_pending) > 0): ?>
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_pending)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['message_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_detail']); ?></td>
                        <td>
                            <a href="update_message.php?message_id=<?php echo $row['message_id']; ?>&message_status=seen" type="button" class="btn btn-primary">Read</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No pending messages</p>
    <?php endif; ?>
</div>

<!-- Seen Messages Section -->
<div class="container table-container">
    <h5 class="text-center">
        Read Messages
    </h5>
    <?php if (mysqli_num_rows($result_seen) > 0): ?>
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_seen)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['message_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['message_detail']); ?></td>
                        <td>
                            <a href="delete.php?message_id=<?php echo $row['message_id']; ?>" type="button" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No seen messages</p>
    <?php endif; ?>
</div>

<?php
include 'footer.php';
?>