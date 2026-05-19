<?php
session_start();
include 'database.php';

if (isset($_GET['review_id'])) {
    $review_id = $_GET['review_id'];

    // Fetch the review details
    $query = "SELECT * FROM review_audio WHERE review_id = '$review_id'";
    $result = mysqli_query($conn, $query);
    $review = mysqli_fetch_assoc($result);

    // Check if the logged-in user is the same as the reviewer
    if ($_SESSION['user_id'] != $review['user_id']) {
        // If the user is not the reviewer, redirect to a page
        header('Location: signin.php');
        exit;
    }

    // Delete the review
    $delete_query = "DELETE FROM review_audio WHERE review_id = '$review_id'";
    mysqli_query($conn, $delete_query);

    // Redirect back to the audio details page
    header('Location: detail.php?audio_id=' . $review['audio_id']);
    exit;
} else {
    echo "Review not found!";
    exit;
}
