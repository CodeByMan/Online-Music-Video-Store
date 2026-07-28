<?php
include 'header.php';

$userData = $current_user;
?>

<div class="container mt-2">
    <div class="row">
        <div class="col-md-12">
            <!-- Profile Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3>Your Profile</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Image and Name -->
                        <div class="col-md-4 text-center">
                            <img src="img/profile-img/<?php echo e($userData['user_image']); ?>" alt="User Image" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
                        </div>
                        <!-- Doctor Details -->
                        <div class="col-md-8">
                            <!-- Contact Info Table -->
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Name:</th>
                                        <td><?php echo e($userData['user_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo e($userData['user_email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Contact No:</th>
                                        <td><?php echo e($userData['user_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td><?php echo e($userData['user_address']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-3">
                                <a href="update_profile.php" class="btn btn-primary">Edit Profile</a>
                                <a href="update_password.php" class="btn btn-danger">Change Password</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End Profile Card -->
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
