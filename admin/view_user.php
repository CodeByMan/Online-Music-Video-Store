<?php
include('header.php');

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

$sum = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$row = mysqli_fetch_assoc($sum);
?>

<!-- Content Start -->
<div class="container table-container">
    <h6 class="text-center">
        Total Users: <?php echo $row['total']; ?>
    </h6>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <!-- Users Table -->
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>

                        <?php
                        $role_id = $row['role_id'];
                        $role_sql = "SELECT role_type FROM roles WHERE role_id = $role_id";
                        $role_result = mysqli_query($conn, $role_sql);
                        $role = mysqli_fetch_assoc($role_result);
                        ?>
                        <td><?php echo htmlspecialchars($role['role_type']); ?></td>
                        <td>
                            <?php if ($role_id != 1): ?>
                                <a href="edit_user.php?user_id=<?php echo $row['user_id'];?>" type="button" class="btn btn-primary">Edit</a>
                                <a href="delete.php?user_id=<?php echo $row['user_id'];?>" type="button" class="btn btn-danger">Delete</a>
                            <?php else: ?>
                                <span class="text-muted">Cannot Edit OR Delete</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No users found</p>
    <?php endif; ?>
</div>
<!-- Content End -->

<?php
include 'footer.php';
?>
