<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        $userEmail = input_email($_POST);
        if (rate_limit_is_blocked($conn, 'frontend_login', $userEmail)) {
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
            && (int) $user['role_id'] === ROLE_USER
            && $user['account_status'] === 'active'
            && password_verify($userPassword, $user['user_password'])) {
            if (password_needs_rehash($user['user_password'], PASSWORD_DEFAULT)) {
                db_execute($conn, 'UPDATE users SET user_password = ? WHERE user_id = ?', 'si', [password_hash($userPassword, PASSWORD_DEFAULT), (int) $user['user_id']]);
            }
            db_execute($conn, 'UPDATE users SET last_login_at = NOW() WHERE user_id = ?', 'i', [(int) $user['user_id']]);
            rate_limit_clear($conn, 'frontend_login', $userEmail);
            establish_authenticated_session($user);
            redirect('index.php');
        }

        rate_limit_hit($conn, 'frontend_login', $userEmail, 5, 900, 900);
        throw new InvalidArgumentException('Invalid credentials.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Frontend login error: ' . $exception->getMessage());
        $message = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Unable to sign in. Please try again.';
        redirect_with_flash('signin.php', $message, 'danger');
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>One Sound - Music Unlimited</title>
    <meta name="description" content="Sign in to One Sound">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Muhammad Ali Nawaz">
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="header-area"><div class="oneMusic-main-menu"><div class="classy-nav-container breakpoint-off"><div class="container"><nav class="classy-navbar justify-content-between" id="oneMusicNav"><a href="home.php" class="nav-brand"><img src="img/core-img/logo.png" alt="One Sound"></a></nav></div></div></div></header>
<section class="breadcumb-area bg-img" style="background-image: url(img/banner/bradcam_2.png);"><div class="bradcumbContent"><p>See what’s new</p><h2>Sign In</h2></div></section>
<?php echo render_flash_messages(); ?>
<section class="login-area section-padding-100">
    <div class="container"><div class="row justify-content-center"><div class="col-12 col-lg-8"><div class="login-content">
        <h3>Welcome Back</h3>
        <div class="login-form"><form action="signin.php" method="post">
            <?php echo csrf_field(); ?>
            <div class="form-group"><label for="signin-email">Email address</label><input id="signin-email" type="email" name="email" class="form-control" aria-describedby="emailHelp" placeholder="Enter E-mail" maxlength="254" required autocomplete="email"><small id="emailHelp" class="form-text text-muted"><i class="fa fa-lock mr-2"></i>We'll never share your email with anyone else.</small></div>
            <div class="form-group"><label for="signin-password">Password</label><input id="signin-password" type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password"></div>
            <button type="submit" class="btn oneMusic-btn mt-30">Sign In</button>
            <p>Don't have an account <a class="small" href="signup.php"><strong>Sign Up</strong></a></p>
        </form></div>
    </div></div></div></div>
</section>
<footer class="footer"><div class="copy-right_text"><div class="container"><div class="footer_border"></div><p class="copy_right text-center">Developed by Muhammad Ali Nawaz</p></div></div></footer>
<script src="js/vendor/modernizr-3.5.0.min.js"></script>
<script src="js/vendor/jquery-3.6.0.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
