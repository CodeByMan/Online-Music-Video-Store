<?php 
include('header.php');

$sql = "SELECT * FROM roles";
$result = mysqli_query($conn, $sql);

$sum = mysqli_query($conn, "SELECT COUNT(*) AS total FROM roles");
$row = mysqli_fetch_assoc($sum);

?>

<!-- Content Start -->
<div class="container table-container">
    <h6 class="text-center">
        Total Roles: <?php echo $row['total']; ?>
    </h6>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <!-- Users Table -->
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Role ID</th>
                    <th>Role Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['role_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['role_type']); ?></td>
                        <td>
                            <!-- Conditional check for role_id = 1 -->
                            <?php if ($row['role_id'] != 1 && $row['role_id'] != 2): ?>
                                <a href="edit_role.php?role_id=<?php echo $row['role_id'];?>" type="button" class="btn btn-primary">Edit</a>
                                <a href="delete.php?role_id=<?php echo $row['role_id'];?>" type="button" class="btn btn-danger">Delete</a>
                            <?php else: ?>
                                <span class="text-muted">Cannot Edit OR Delete</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No roles found</p>
    <?php endif; ?>
</div>
<!-- Content End -->

<?php
include 'footer.php';
?>
