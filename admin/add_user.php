<?php
include("header.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['user_name'];
    $user_phone = $_POST['user_phone'];
    $user_address = $_POST['user_address'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];
    $role_id = $_POST['role_id'];

    $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO `users`(`user_name`, `user_phone`, `user_address`, `user_email`, `user_password`, `role_id`) 
            VALUES ('$user_name', '$user_phone', '$user_address', '$user_email', '$hashed_password', '$role_id')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('User Added Successfully'); window.location.href='view_user.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

$sql_roles = "SELECT * FROM roles WHERE role_id != 1";
$result_roles = mysqli_query($conn, $sql_roles);

?>

<div class="container mt-5">
    <form action="add_user.php" method="POST">
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group">
                    <label>User Name:</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" required>
                </div>
            </div>

            <div class="col-md-6"> 
                <div class="form-group">
                    <label>User Phone:</label>
                    <input type="text" class="form-control" id="user_phone" name="user_phone" required>
                </div>
            </div>

            <div class="col-md-6"> 
                <div class="form-group">
                    <label>User Address:</label>
                    <input type="text" class="form-control" id="user_address" name="user_address" required>
                </div>
            </div>

            <div class="col-md-6"> 
                <div class="form-group">
                    <label>User Email:</label>
                    <input type="email" class="form-control" id="user_email" name="user_email" required>
                </div>
            </div>

            <div class="col-md-6"> 
                <div class="form-group">
                    <label>User Password:</label>
                    <input type="password" class="form-control" id="user_password" name="user_password" required>
                </div>
            </div>
            
            <div class="col-md-12"> 
                <div class="form-group">
                    <label>Role:</label>
                    <select class="form-control" name="role_id" required>
                        <option value="">Select Role</option>
                        <?php while ($role = mysqli_fetch_assoc($result_roles)): ?>
                            <option value="<?php echo $role['role_id']; ?>"><?php echo $role['role_type']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

        </div>
        
        <button type="submit" class="btn btn-primary d-block w-100">Add User</button>
    </form>
</div>

<?php
include 'footer.php';
?>
