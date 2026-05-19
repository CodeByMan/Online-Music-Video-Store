<?php
include("header.php");

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $profile = mysqli_fetch_assoc($result);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_name = $_POST['name'];
        $user_phone = $_POST['phone'];
        $user_address = $_POST['address'];
        $user_image = $_FILES['image']['name'];

        if ($user_image) {
            $target_dir = "img/profile-img/";
            $target_file = $target_dir . basename($user_image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        } else {
            $user_image = $profile['user_image'];
        }

        $update_sql = "UPDATE users SET 
                        user_name = '$user_name',
                        user_phone = '$user_phone',
                        user_address = '$user_address',
                        user_image = '$user_image'
                        WHERE user_id = '$user_id'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Profile Updated Successfully!'); window.location.href='view_profile.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
} else {
    echo "No user ID provided.";
    exit;
}
?>

<!-- Edit Form  -->
<div class="container-fluid pt-4 px-4">  
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <form class="row" action="#" method="post" enctype="multipart/form-data">
                    
                    <div class="col-md-12 mt-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $profile['user_name']; ?>" required>
                    </div>

                    <div class="col-md-12 mt-2">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $profile['user_phone']; ?>" required>
                    </div>

                    <div class="col-md-12 mt-2">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo $profile['user_address']; ?>" required>
                    </div>

                    <div class="col-md-12 mt-2">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="col-md-12 mt-3">
                        <button class="btn btn-primary d-block w-100" type="submit">Update Profile</button>
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
