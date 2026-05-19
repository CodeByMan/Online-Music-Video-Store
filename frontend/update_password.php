<?php
session_start();
include 'header.php';

if (!$is_logged_in) {
    echo "You need to log in to access this page.";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
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
                echo "<script>alert('Password Updated Successfully!'); window.location.href='index.php';</script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    }
} else {
    echo "User profile not found.";
    exit();
}
?>

<!-- bradcam_area -->
<div class="bradcam_area breadcam_bg_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text text-center">
                    <h3>Update</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

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
                        <button class="btn btn-danger d-block w-100" type="submit">Change Password</button>
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
