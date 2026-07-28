<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$user_id = (int) $current_user['user_id'];
$profile = $current_user;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $newImage = null;
    try {
        verify_csrf();
        $name = input_text($_POST, 'name', 2, 100);
        $phone = input_phone($_POST, 'phone');
        $address = input_text($_POST, 'address', 3, 255);
        $image = (string) $profile['user_image'];
        if (uploaded_file_present($_FILES['image'] ?? [])) {
            $upload = store_uploaded_file($_FILES['image'], __DIR__ . '/img/profile-img', 'image', 'user_');
            $newImage = $upload['stored_name'];
            $image = $newImage;
        }
        try {
            db_execute($conn, 'UPDATE users SET user_name = ?, user_phone = ?, user_address = ?, user_image = ? WHERE user_id = ?', 'ssssi', [$name, $phone, $address, $image, $user_id]);
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                delete_file_if_safe(__DIR__ . '/img/profile-img', $newImage, 'user_');
            }
            throw $exception;
        }
        if ($newImage !== null) {
            delete_file_if_safe(__DIR__ . '/img/profile-img', (string) $profile['user_image'], 'user_');
        }
        $_SESSION['user_name'] = $name;
        redirect_with_flash('view_profile.php', 'Profile updated successfully.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Admin profile update error: ' . $exception->getMessage());
        redirect_with_flash('view_profile.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the profile. Please try again.', 'danger');
    }
}
include 'header.php';
?>
<div class="container-fluid pt-4 px-4"><div class="row g-4"><div class="col-12"><div class="bg-light rounded h-100 p-4"><form class="row" action="update_profile.php" method="post" enctype="multipart/form-data">
<?php echo csrf_field(); ?>
<div class="col-md-12 mt-2"><label class="form-label" for="profile-name">Full Name</label><input id="profile-name" type="text" name="name" class="form-control" value="<?php echo e($profile['user_name']); ?>" maxlength="100" required></div>
<div class="col-md-12 mt-2"><label class="form-label" for="profile-phone">Phone Number</label><input id="profile-phone" type="tel" name="phone" class="form-control" value="<?php echo e($profile['user_phone']); ?>" maxlength="30" required></div>
<div class="col-md-12 mt-2"><label class="form-label" for="profile-address">Address</label><input id="profile-address" type="text" name="address" class="form-control" value="<?php echo e($profile['user_address']); ?>" maxlength="255" required></div>
<div class="col-md-12 mt-2"><label class="form-label" for="profile-image">Profile Image</label><input id="profile-image" type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
<div class="col-md-12 mt-3"><button class="btn btn-primary d-block w-100" type="submit">Update Profile</button></div>
</form></div></div></div></div>
<?php include 'footer.php'; ?>
