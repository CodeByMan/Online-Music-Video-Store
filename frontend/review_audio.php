<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to submit a review.";
    exit;
}

if (isset($_GET['audio_id'])) {
    $audio_id = $_GET['audio_id'];
} else {
    echo "Audio not found!";
    exit;
}

$query = "SELECT audio_name FROM audio WHERE audio_id = '$audio_id'";
$result = mysqli_query($conn, $query);
$audio = mysqli_fetch_assoc($result);
$audio_name = htmlspecialchars($audio['audio_name']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    $rating = $_POST['rating'];

    $user_id = $_SESSION['user_id'];
    
    $insert_query = "
        INSERT INTO review_audio (review_text, review_rating, audio_id, user_id)
        VALUES ('$review_text', '$rating', '$audio_id', '$user_id')
    ";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Review submitted successfully'); window.location.href='greeting.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
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

<div class="container mt-5">
    <h2>Review Audio: <?php echo $audio_name; ?></h2>
    <p>Add New Review</p>
    
    <!-- Review Form -->
    <form method="POST" action="review_audio.php?audio_id=<?php echo $audio_id; ?>">
        <div class="form-group">
            <label for="review_text">Your Review</label>
            <textarea id="review_text" name="review_text" class="form-control" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="rating">Rating (1 to 5)</label>
            <input type="number" id="rating" name="rating" class="form-control" min="1" max="5" required>
        </div>
        
        <button type="submit" class="btn btn-success">Submit Review</button>
    </form>
</div>

 <!-- JS here -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" 
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" 
            crossorigin="anonymous">
    </script>
    <script src="js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/ajax-form.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/imagesloaded.pkgd.min.js"></script>
    <script src="js/audioplayer.js"></script>
    <script src="js/scrollIt.js"></script>
    <script src="js/jquery.scrollUp.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/nice-select.min.js"></script>
    <script src="js/jquery.slicknav.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/gijgo.min.js"></script>
    <script src="js/slick.min.js"></script>
    <!--contact js-->
    <script src="js/contact.js"></script>
    <script src="js/jquery.ajaxchimp.min.js"></script>
    <script src="js/jquery.form.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/mail-script.js"></script>

    <script src="js/main.js"></script>
</body>

</html>


