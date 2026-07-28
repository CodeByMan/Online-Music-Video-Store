<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$roles = [ROLE_ADMIN => 'Administrator', ROLE_USER => 'User'];
try {
    $editUserId = request_positive_int($_GET, 'user_id');
    $user = db_fetch_one($conn, 'SELECT user_id, user_name, user_email, role_id, account_status, user_image FROM users WHERE user_id = ? LIMIT 1', 'i', [$editUserId]);
    if ($user === null) {
        throw new RuntimeException('The selected user was not found.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $roleId = request_positive_int($_POST, 'role_id');
        if (!isset($roles[$roleId])) {
            throw new InvalidArgumentException('Please select a valid role.');
        }
        $status = input_text($_POST, 'account_status', 6, 8);
        if (!in_array($status, ['active', 'disabled'], true)) {
            throw new InvalidArgumentException('Please select a valid account status.');
        }

        $oldImage = null;
        $oldRole = null;
        $conn->begin_transaction();
        try {
            $lockedUser = db_fetch_one($conn, 'SELECT user_id, role_id, account_status, user_image FROM users WHERE user_id = ? FOR UPDATE', 'i', [$editUserId]);
            if ($lockedUser === null) {
                throw new RuntimeException('The selected user was not found.');
            }
            if ($editUserId === (int) $current_user['user_id'] && ($roleId !== ROLE_ADMIN || $status !== 'active')) {
                throw new RuntimeException('You cannot remove or disable your own administrator access.');
            }

            $removingActiveAdmin = (int) $lockedUser['role_id'] === ROLE_ADMIN
                && $lockedUser['account_status'] === 'active'
                && ($roleId !== ROLE_ADMIN || $status !== 'active');
            if ($removingActiveAdmin) {
                $activeAdmins = db_fetch_all($conn, "SELECT user_id FROM users WHERE role_id = ? AND account_status = 'active' FOR UPDATE", 'i', [ROLE_ADMIN]);
                if (count($activeAdmins) <= 1) {
                    throw new RuntimeException('At least one active administrator account must remain.');
                }
            }

            $roleChanged = (int) $lockedUser['role_id'] !== $roleId;
            $image = $roleChanged ? 'vector.jpg' : (string) $lockedUser['user_image'];
            if ($roleChanged && $lockedUser['user_image'] !== 'vector.jpg') {
                $oldImage = (string) $lockedUser['user_image'];
                $oldRole = (int) $lockedUser['role_id'];
            }

            db_execute(
                $conn,
                "UPDATE users SET role_id = ?, account_status = ?, disabled_at = IF(? = 'disabled', COALESCE(disabled_at, NOW()), NULL), user_image = ? WHERE user_id = ?",
                'isssi',
                [$roleId, $status, $status, $image, $editUserId]
            );
            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollback();
            throw $exception;
        }

        if ($oldImage !== null && $oldRole !== null) {
            $oldDirectory = $oldRole === ROLE_ADMIN ? __DIR__ . '/img/profile-img' : dirname(__DIR__) . '/frontend/img/banner';
            delete_file_if_safe($oldDirectory, $oldImage, 'user_');
        }
        redirect_with_flash('view_user.php', 'User updated successfully.');
    }
} catch (Throwable $exception) {
    error_log('[One Sound] Edit user error: ' . $exception->getMessage());
    redirect_with_flash('view_user.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to update the user.', 'danger');
}
include 'header.php';
?>
<div class="container-fluid pt-4 px-4"><div class="row g-4"><div class="col-12"><div class="bg-light rounded h-100 p-4"><form class="row" action="edit_user.php?user_id=<?php echo (int) $editUserId; ?>" method="post">
<?php echo csrf_field(); ?>
<div class="col-md-12"><label class="form-label" for="display-name">User:</label><input id="display-name" type="text" class="form-control" value="<?php echo e($user['user_name']); ?>" readonly></div>
<div class="col-md-12"><label class="form-label mt-2" for="display-email">Email:</label><input id="display-email" type="email" class="form-control" value="<?php echo e($user['user_email']); ?>" readonly></div>
<div class="col-md-6"><label class="form-label mt-2" for="role_id">Role:</label><select id="role_id" class="form-control" name="role_id" required><?php foreach ($roles as $id => $label): ?><option value="<?php echo $id; ?>" <?php echo $id === (int) $user['role_id'] ? 'selected' : ''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label mt-2" for="account_status">Status:</label><select id="account_status" class="form-control" name="account_status" required><option value="active" <?php echo $user['account_status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="disabled" <?php echo $user['account_status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option></select></div>
<div class="col-md-12 mt-3"><button class="btn btn-primary d-block w-100" type="submit">Update User</button></div>
</form></div></div></div></div>
<?php include 'footer.php'; ?>
