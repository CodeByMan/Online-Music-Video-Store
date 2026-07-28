<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        enforce_action_rate_limit($conn, 'registration', 'registration', 5, 3600, 3600);

        $userName = input_text($_POST, 'name', 2, 100);
        $userPhone = input_phone($_POST, 'phone');
        $userAddress = input_text($_POST, 'address', 3, 255);
        $userEmail = input_email($_POST);
        $userPassword = input_password($_POST, 'password');

        if (db_fetch_one($conn, 'SELECT user_id FROM users WHERE user_email = ? LIMIT 1', 's', [$userEmail]) !== null) {
            throw new InvalidArgumentException('An account with this email already exists.');
        }

        try {
            db_execute(
                $conn,
                'INSERT INTO users (user_name, user_phone, user_address, user_email, user_password, role_id) VALUES (?, ?, ?, ?, ?, ?)',
                'sssssi',
                [$userName, $userPhone, $userAddress, $userEmail, password_hash($userPassword, PASSWORD_DEFAULT), ROLE_USER]
            );
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                throw new InvalidArgumentException('An account with this email already exists.', 0, $exception);
            }
            throw $exception;
        }

        redirect_with_flash('signin.php', 'Successfully signed up. You can now sign in.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Registration error: ' . $exception->getMessage());
        $message = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Unable to create the account. Please try again.';
        redirect_with_flash('signup.php', $message, 'danger');
    }
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>One Sound - Music Unlimited</title>
    <meta name="description" content="Create a One Sound account">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Muhammad Ali Nawaz">
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="header-area"><div class="oneMusic-main-menu"><div class="classy-nav-container breakpoint-off"><div class="container"><nav class="classy-navbar justify-content-between" id="oneMusicNav"><a href="home.php" class="nav-brand"><img src="img/core-img/logo.png" alt="One Sound"></a></nav></div></div></div></header>
<section class="breadcumb-area bg-img" style="background-image: url(img/banner/bradcam_2.png);"><div class="bradcumbContent"><p>See what’s new</p><h2>Register</h2></div></section>
<?php echo render_flash_messages(); ?>
<section class="login-area section-padding-100"><div class="container"><div class="row justify-content-center"><div class="col-12 col-lg-8"><div class="login-content">
<h3>Sign Up for Free</h3><div class="login-form"><form action="signup.php" method="post">
<?php echo csrf_field(); ?>
<div class="form-group"><label for="fullName">Full Name</label><input type="text" name="name" class="form-control" id="fullName" placeholder="Enter Full Name" maxlength="100" required autocomplete="name"></div>
<div class="form-group"><label for="phone">Phone No.</label><input type="tel" name="phone" class="form-control" id="phone" placeholder="Enter Phone Number" maxlength="30" required autocomplete="tel"></div>
<div class="form-group"><label for="address">Address</label><textarea name="address" class="form-control" id="address" placeholder="Enter Address" maxlength="255" required autocomplete="street-address"></textarea></div>
<div class="form-group"><label for="signup-email">Email address</label><input id="signup-email" type="email" name="email" class="form-control" aria-describedby="emailHelp" placeholder="Enter E-mail" maxlength="254" required autocomplete="email"><small id="emailHelp" class="form-text text-muted"><i class="fa fa-lock mr-2"></i>We'll never share your email with anyone else.</small></div>
<div class="form-group"><label for="signup-password">Password</label><input id="signup-password" type="password" name="password" class="form-control" placeholder="Password" minlength="8" maxlength="72" required autocomplete="new-password"></div>
<button type="submit" class="btn oneMusic-btn mt-30">Sign up</button><p>Already have an account? <a class="small" href="signin.php"><strong>Sign In</strong></a></p>
</form></div></div></div></div></div></section>
<footer class="footer"><div class="copy-right_text"><div class="container"><div class="footer_border"></div><p class="copy_right text-center">Developed by Muhammad Ali Nawaz</p></div></div></footer>
<script src="js/vendor/modernizr-3.5.0.min.js"></script>
<script src="js/vendor/jquery-3.6.0.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
