<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_admin($conn);
$roles = [ROLE_ADMIN => 'Administrator', ROLE_USER => 'User'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        $name = input_text($_POST, 'user_name', 2, 100);
        $phone = input_phone($_POST, 'user_phone');
        $address = input_text($_POST, 'user_address', 3, 255);
        $email = input_email($_POST, 'user_email');
        $password = input_password($_POST, 'user_password');
        $roleId = request_positive_int($_POST, 'role_id');
        if (!isset($roles[$roleId])) {
            throw new InvalidArgumentException('Please select a valid role.');
        }
        $status = input_text($_POST, 'account_status', 6, 8);
        if (!in_array($status, ['active', 'disabled'], true)) {
            throw new InvalidArgumentException('Please select a valid account status.');
        }
        if (db_fetch_one($conn, 'SELECT user_id FROM users WHERE user_email = ? LIMIT 1', 's', [$email]) !== null) {
            throw new InvalidArgumentException('An account with this email already exists.');
        }
        try {
            db_execute(
                $conn,
                "INSERT INTO users (user_name, user_phone, user_address, user_email, user_password, role_id, account_status, disabled_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, IF(? = 'disabled', NOW(), NULL))",
                'sssssiss',
                [$name, $phone, $address, $email, password_hash($password, PASSWORD_DEFAULT), $roleId, $status, $status]
            );
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                throw new InvalidArgumentException('An account with this email already exists.', 0, $exception);
            }
            throw $exception;
        }
        redirect_with_flash('view_user.php', 'User added successfully.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Add user error: ' . $exception->getMessage());
        redirect_with_flash('add_user.php', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : 'Unable to add the user. Please try again.', 'danger');
    }
}
include 'header.php';
?>
<div class="container mt-5"><form action="add_user.php" method="POST"><?php echo csrf_field(); ?><div class="row">
<div class="col-md-12"><div class="form-group"><label for="user_name">User Name:</label><input type="text" class="form-control" id="user_name" name="user_name" maxlength="100" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="user_phone">User Phone:</label><input type="tel" class="form-control" id="user_phone" name="user_phone" maxlength="30" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="user_address">User Address:</label><input type="text" class="form-control" id="user_address" name="user_address" maxlength="255" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="user_email">User Email:</label><input type="email" class="form-control" id="user_email" name="user_email" maxlength="254" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="user_password">User Password:</label><input type="password" class="form-control" id="user_password" name="user_password" minlength="8" maxlength="72" required autocomplete="new-password"></div></div>
<div class="col-md-6"><div class="form-group"><label for="role_id">User Role:</label><select id="role_id" class="form-control" name="role_id" required><option value="">Select Role</option><?php foreach ($roles as $id => $label): ?><option value="<?php echo $id; ?>"><?php echo e($label); ?></option><?php endforeach; ?></select></div></div>
<div class="col-md-6"><div class="form-group"><label for="account_status">Account Status:</label><select id="account_status" class="form-control" name="account_status" required><option value="active">Active</option><option value="disabled">Disabled</option></select></div></div>
</div><button type="submit" class="btn btn-primary d-block w-100">Add User</button></form></div>
<?php include 'footer.php'; ?>
