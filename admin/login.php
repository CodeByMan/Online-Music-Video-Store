<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        $userEmail = input_email($_POST);

        if (rate_limit_is_blocked($conn, 'admin_login', $userEmail)) {
            throw new RuntimeException('Too many login attempts. Please wait and try again.');
        }

        $userPassword = $_POST['password'] ?? '';
        if (!is_string($userPassword) || $userPassword === '' || strlen($userPassword) > 4096) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        $user = db_fetch_one(
            $conn,
            "SELECT user_id, user_name, user_password, role_id, account_status, session_version
             FROM users WHERE user_email = ? LIMIT 1",
            's',
            [$userEmail]
        );

        if ($user !== null
            && (int) $user['role_id'] === ROLE_ADMIN
            && $user['account_status'] === 'active'
            && password_verify($userPassword, $user['user_password'])) {
            if (password_needs_rehash($user['user_password'], PASSWORD_DEFAULT)) {
                db_execute($conn, 'UPDATE users SET user_password = ? WHERE user_id = ?', 'si', [password_hash($userPassword, PASSWORD_DEFAULT), (int) $user['user_id']]);
            }
            db_execute($conn, 'UPDATE users SET last_login_at = NOW() WHERE user_id = ?', 'i', [(int) $user['user_id']]);
            rate_limit_clear($conn, 'admin_login', $userEmail);
            establish_authenticated_session($user);
            redirect('index.php');
        }

        rate_limit_hit($conn, 'admin_login', $userEmail, 5, 900, 900);
        throw new InvalidArgumentException('Invalid credentials.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Admin login error: ' . $exception->getMessage());
        $message = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Unable to sign in. Please try again.';
        redirect_with_flash('login.php', $message, 'danger');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="One Sound administrator login">
    <meta name="author" content="Muhammad Ali Nawaz">
    <title>One Sound - Admin Login</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../frontend/home.php"><img src="img/logo1.png" alt="One Sound" width="150"></a>
    </div>
</nav>
<?php echo render_flash_messages(); ?>
<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="row justify-content-center w-100">
        <div class="col-xl-6 col-lg-8 col-md-10">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center mb-4"><h1 class="h4 text-gray-900">Admin Login</h1></div>
                                <form action="login.php" method="post" class="user">
                                    <?php echo csrf_field(); ?>
                                    <div class="form-group">
                                        <label class="sr-only" for="admin-email">Email address</label>
                                        <input type="email" name="email" class="form-control form-control-user" id="admin-email" placeholder="Enter Email Address..." maxlength="254" required autocomplete="email">
                                    </div>
                                    <div class="form-group">
                                        <label class="sr-only" for="admin-password">Password</label>
                                        <input type="password" name="password" class="form-control form-control-user" id="admin-password" placeholder="Password" required autocomplete="current-password">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">Sign In</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="bg-white py-3"><div class="container text-center"><span>Developed by Muhammad Ali Nawaz</span></div></footer>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html>
