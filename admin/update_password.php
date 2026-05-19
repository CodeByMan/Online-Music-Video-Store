<?php
include("header.php");

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $profile = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_password = $_POST['user_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($user_password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

        $update_sql = "UPDATE users SET user_password = '$hashed_password' WHERE user_id = '$user_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Password Updated Successfully!'); window.location.href='view_profile.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
}
?>

<!-- Password Update Form -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <?php if (isset($error_message)) { echo "<p class='text-danger'>$error_message</p>"; } ?>

                <form class="row" action="#" method="post">

                    <!-- New Password -->
                    <div class="col-md-12 mt-2">
                        <label class="form-label">New Password</label>
                        <input type="password" name="user_password" class="form-control" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-12 mt-3">
                        <button class="btn btn-primary d-block w-100" type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php 
include("footer.php"); 
?>
