<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$current_user = $current_user ?? require_frontend_user($conn);
$is_logged_in = true;
$user_id = (int) $current_user['user_id'];
$user_name = $current_user['user_name'];
$user_image = $current_user['user_image'];
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>One Sound - Music Unlimited</title>
    <meta name="description" content="One Sound music and video platform">
    <meta name="author" content="Muhammad Ali Nawaz">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/themify-icons.css">
    <link rel="stylesheet" href="css/nice-select.css">
    <link rel="stylesheet" href="css/audioplayer.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/gijgo.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/slick.css">
    <link rel="stylesheet" href="css/slicknav.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <!-- header-start -->
    <header>
        <div class="header-area">
            <div id="sticky-header" class="main-header-area">
                <div class="container-fluid">
                    <div class="header_bottom_border">
                        <div class="row align-items-center">
                            <div class="col-6 col-lg-5">
                                <div class="logo">
                                    <a href="index.php">
                                        <img src="img/core-img/logo.png" alt="One Sound">
                                    </a>
                                </div>
                            </div>
                            <div class="col-6 col-lg-7">
                                <div class="main-menu d-none d-lg-block">
                                    <nav>
                                        <ul id="navigation" class="ms-auto">
                                            <li><a href="index.php">Home</a></li>
                                            <li><a href="music.php">Music</a></li>
                                            <li><a href="video.php">Videos</a></li>
                                            <li><a href="gallery.php">Gallery</a></li>
                                            <li><a href="contact.php">Contact</a></li>
                                            <li>
                                                <a href="#" class="text-danger">
                                                    <span class="badge custom-badge bg-danger text-white pt-2">Profile</span>
                                                    <?php echo e($user_name); ?>
                                                    <i class="ti-angle-down"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="profile.php">
                                                        View Profile
                                                        <img class="img-profile rounded-circle" src="img/banner/<?php echo e($user_image); ?>" alt="Profile image" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </a>
                                                    </li>
                                                    <hr>
                                                    <li>
                                                        <a href="logout.php" onclick="event.preventDefault(); document.getElementById('frontend-logout-form').submit();">Logout</a>
                                                        <form id="frontend-logout-form" action="logout.php" method="post" style="display: none;">
                                                            <?php echo csrf_field(); ?>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="col-12 d-block d-lg-none mt-2">
                                <div class="mobile_menu d-block d-lg-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header-end -->
    <?php echo render_flash_messages(); ?>
