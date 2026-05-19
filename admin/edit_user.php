<?php 
include('header.php');

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    $sql_roles = "SELECT * FROM roles where role_id != 1";
    $result_roles = mysqli_query($conn, $sql_roles);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_password = $_POST['user_password'];
        $role_id = $_POST['role_id'];

        $update_sql = "UPDATE users SET 
                        role_id = '$role_id'
                        WHERE user_id = '$user_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('User Updated Successfully!'); window.location.href='view_user.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!-- Edit User Form -->
<div class="container-fluid pt-4 px-4">  
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <form class="row" action="#" method="post">
                    <div class="col-md-12">
                        <label class="form-label">User:</label>
                        <input type="text" name="user_name" class="form-control" value="<?php echo $user['user_name']; ?>" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label mt-2">Email:</label>
                        <input type="email" name="user_email" class="form-control" value="<?php echo $user['user_email']; ?>" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label mt-2">Role:</label>
                        <select class="form-control" name="role_id" required>
                            <?php while ($role = mysqli_fetch_assoc($result_roles)): ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo ($role['role_id'] == $user['role_id']) ? 'selected' : ''; ?>>
                                    <?php echo $role['role_type']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-12 mt-3">
                        <button class="btn btn-primary d-block w-100" type="submit">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
