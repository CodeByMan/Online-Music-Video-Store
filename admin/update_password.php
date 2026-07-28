<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$user_id = (int) $current_user['user_id'];
$passwordRow = db_fetch_one($conn, 'SELECT user_password FROM users WHERE user_id = ? LIMIT 1', 'i', [$user_id]);
if ($passwordRow === null) {
    clear_authenticated_session();
    redirect('login.php');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        $currentPassword = $_POST['current_password'] ?? '';
        if (!is_string($currentPassword) || !password_verify($currentPassword, (string) $passwordRow['user_password'])) {
            throw new InvalidArgumentException('Current password is incorrect.');
        }
        $newPassword = input_password($_POST, 'user_password');
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (!is_string($confirmPassword) || !hash_equals($newPassword, $confirmPassword)) {
            throw new InvalidArgumentException('Passwords do not match.');
        }
        if (password_verify($newPassword, (string) $passwordRow['user_password'])) {
            throw new InvalidArgumentException('New password must be different from the current password.');
        }

        db_execute($conn, 'UPDATE users SET user_password = ?, session_version = session_version + 1 WHERE user_id = ?', 'si', [password_hash($newPassword, PASSWORD_DEFAULT), $user_id]);
        session_regenerate_id(true);
        set_flash('success', 'Password updated successfully. Please sign in again.');
        clear_authenticated_session(true);
        redirect('login.php');
    } catch (Throwable $exception) {
        error_log('[One Sound] Admin password update error: ' . $exception->getMessage());
        set_flash('danger', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the password. Please try again.');
        redirect('update_password.php');
    }
}
include 'header.php';
?>
<div class="container-fluid pt-4 px-4"><div class="row g-4"><div class="col-12"><div class="bg-light rounded h-100 p-4">
<form class="row" action="update_password.php" method="post">
<?php echo csrf_field(); ?>
<div class="col-md-12 mt-2"><label class="form-label" for="current_password">Current Password</label><input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
<div class="col-md-12 mt-2"><label class="form-label" for="user_password">New Password</label><input id="user_password" type="password" name="user_password" class="form-control" minlength="8" maxlength="72" required autocomplete="new-password"></div>
<div class="col-md-12 mt-2"><label class="form-label" for="confirm_password">Confirm Password</label><input id="confirm_password" type="password" name="confirm_password" class="form-control" minlength="8" maxlength="72" required autocomplete="new-password"></div>
<div class="col-md-12 mt-3"><button class="btn btn-primary d-block w-100" type="submit">Update Password</button></div>
</form></div></div></div></div>
<?php include 'footer.php'; ?>
