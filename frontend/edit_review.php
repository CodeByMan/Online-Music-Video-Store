<?php 
session_start();
include 'database.php'; 

if (isset($_GET['review_id'])) {
    $review_id = $_GET['review_id'];

    $query = "SELECT * FROM review_audio WHERE review_id = '$review_id'";
    $result = mysqli_query($conn, $query);
    $review = mysqli_fetch_assoc($result);

    if ($_SESSION['user_id'] != $review['user_id']) {
        header('Location: signin.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $review_text = $_POST['review_text'];
        $review_rating = $_POST['review_rating'];

        $review_text = mysqli_real_escape_string($conn, $review_text);
        $review_rating = (int)$review_rating;

        if ($review_rating < 1 || $review_rating > 5) {
            $error_message = "Rating must be between 1 and 5.";
        } else {

            $update_query = "UPDATE review_audio SET review_text = '$review_text', review_rating = '$review_rating' WHERE review_id = '$review_id'";
            if (mysqli_query($conn, $update_query)) {

                echo "<script>alert('Review Updated successfully'); window.location.href='greeting.php';</script>";
                exit;
            } else {
                $error_message = "Error updating review. Please try again.";
            }
        }
    }

} else {
    echo "Review not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1>Edit Your Review</h1>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <label for="review_text">Review Text:</label>
            <textarea id="review_text" name="review_text" class="form-control" rows="4" placeholder="Write your review..."><?php echo htmlspecialchars($review['review_text']); ?></textarea>
            
            
            <div class="form-group">
                <label for="review_rating">Rating (1 to 5):</label>
                <input type="number" id="rating" class="form-control" name="review_rating" value="<?php echo $review['review_rating']; ?>" min="1" max="5" placeholder="Rate between 1 to 5">
            </div>

            <button type="submit" class="btn btn-success">Update Review</button>
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
