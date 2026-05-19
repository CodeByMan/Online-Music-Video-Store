<?php
include 'database.php';

$is_logged_in = isset($_SESSION['user_name']) && isset($_SESSION['user_id']);

if (!$is_logged_in && basename($_SERVER['PHP_SELF']) !== 'signin.php') {
    header("Location: signin.php");
    exit();
}

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];

    $user = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($user)) {
        $user_name = $row['user_name'];
        $user_image = $row['user_image'];
    }
}
?>
<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>One Sound - Music Unlimited</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- <link rel="manifest" href="site.webmanifest"> -->
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">
    <!-- Place favicon.ico in the root directory -->

    <!-- CSS here -->
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
    <!-- <link rel="stylesheet" href="css/responsive.css"> -->
</head>

<body>

    <!-- header-start -->
    <header>
        <div class="header-area">
            <div id="sticky-header" class="main-header-area">
                <div class="container-fluid">
                    <div class="header_bottom_border">
                        <div class="row align-items-center">
                            <!-- Logo on the left side -->
                            <div class="col-6 col-lg-5">
                                <div class="logo">
                                    <a href="index.php">
                                        <img src="img/core-img/logo.png" alt="">
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Navigation menu on the right side -->
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
                                                    <?php 
                                                    if ($is_logged_in) {
                                                        echo htmlspecialchars($_SESSION['user_name']);
                                                    }
                                                    ?>
                                                    <i class="ti-angle-down"></i>
                                                </a>
                                                <ul class="submenu">
                                                    <li><a href="profile.php">
                                                        View Profile
                                                        <img class="img-profile rounded-circle" src="img/banner/<?php echo $user_image; ?>" alt="img" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </a>
                                                    </li>
                                                    <hr>
                                                    <li><a href="logout.php">Logout</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <!-- Mobile menu (hidden on larger screens) -->
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
