<?php
session_start();
include 'header.php';

if (isset($_GET['audio_id'])) {
    $audio_id = $_GET['audio_id'];

    $audio_query = "
        SELECT a.audio_name, c.category_album, c.category_artist, c.category_year, c.category_language, 
               c.category_image, c.category_genre
        FROM audio a
        JOIN categories c ON a.category_id = c.category_id
        WHERE a.audio_id = '$audio_id'";
    $audio_result = mysqli_query($conn, $audio_query);

    if (mysqli_num_rows($audio_result) > 0) {
        $audio = mysqli_fetch_assoc($audio_result);
    } else {
        echo "<p>Audio not found!</p>";
        exit;
    }

    $reviews_query = "
        SELECT r.review_id, r.review_text, r.review_rating, u.user_name, r.review_date, r.user_id AS reviewer_id, u.user_image
        FROM review_audio r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.audio_id = '$audio_id'
        ORDER BY r.review_date DESC";
    $reviews_result = mysqli_query($conn, $reviews_query);

    if (!$reviews_result) {
        die("Error executing reviews query: " . mysqli_error($conn));
    }

    $result_audio_query = "
        SELECT a.audio_id, a.audio_name, c.category_album, c.category_artist, c.category_genre, c.category_year
        FROM audio a
        JOIN categories c ON a.category_id = c.category_id
        WHERE a.audio_id = '$audio_id'";
    $result_audio = mysqli_query($conn, $result_audio_query);

} else {
    echo "<p>Audio ID not provided!</p>";
    exit;
}
?>

<!-- bradcam_area -->
<div class="bradcam_area breadcam_bg_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text text-center">
                    <h3>Music</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<!-- ##### Audio Music Area Start ##### -->
<section class="oneMusic-buy-now-area has-fluid section-padding-100">
    <div class="container">
        <?php if (isset($audio)): ?>
            <!-- Audio Details Card -->
            <div class="row mb-4">
                <div class="card mb-3 border-0" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <!-- Ensure the image path exists and is correct -->
                            <img src="../admin/uploads/images/<?php echo htmlspecialchars($audio['category_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($audio['category_album']); ?>" style="width: 700px">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body" style="font-size: 1.2rem;">
                                <h5 class="card-title" style="font-size: 1.5rem;"><?php echo htmlspecialchars($audio['category_album']); ?></h5>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Artist:</strong> <?php echo htmlspecialchars($audio['category_artist']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Genre:</strong> <?php echo htmlspecialchars($audio['category_genre']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Language:</strong> <?php echo htmlspecialchars($audio['category_language']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Year:</strong> <?php echo htmlspecialchars($audio['category_year']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Song List Section -->
            <div class="row mb-4">
                <!-- Table to display songs -->
                <div class="table-responsive mt-4">
                    <!-- Audio Files Table -->
                    <?php if (mysqli_num_rows($result_audio) > 0): ?>
                        <table class="table table-bordered" style="table-layout: fixed;">
                            <thead class="bg-danger text-white">
                                <tr>
                                    <th style="width: 30%;">Song</th>
                                    <th style="width: 50%;">Player</th>
                                    <th style="width: 20%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_audio)): ?>
                                    <tr>
                                        <td><strong style="font-size: 1.2rem;"><?php echo htmlspecialchars($row['audio_name']); ?></strong></td>
                                        <td>
                                            <audio controls style="width: 100%;">
                                                <source src="../admin/uploads/music/<?php echo htmlspecialchars($row['audio_name']); ?>" type="audio/mp3">
                                                Your browser does not support the audio element.
                                            </audio>
                                        </td>
                                        <td>
                                            <div class="play-icon">
                                                <a class="popup-video btn btn-danger mx-2" href="review_audio.php?audio_id=<?php echo htmlspecialchars($row['audio_id']); ?>">Review</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No audio files found.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Audio not found!</p>
        <?php endif; ?>
        <div class="d-flex justify-content-center">
            <a href="search_audio.php" class="btn oneMusic-btn">Search More Music</a>
        </div>
    </div>
</section>
<!-- ##### Audio Music Area End ##### -->

<!-- Reviews Section -->
<section class="oneMusic-buy-now-area has-fluid bg-light">
    <div class="container">
        <div class="comments-area">
            <h4 class="mb-4">Comments</h4>
            <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="comment-list p-3 border rounded">
                    <div class="single-comment">
                        <div class="desc">
                            <div class="user-info d-flex align-items-center mb-2">
                                <!-- Display user image -->
                                <img src="img/banner/<?php echo htmlspecialchars($review['user_image']); ?>" alt="User Image" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                
                                <!-- User name and review date -->
                                <div>
                                    <p class="mb-1"><?php echo htmlspecialchars($review['user_name']); ?></p>
                                </div>
                            </div>
                            <p class="comment">Comments: <?php echo htmlspecialchars($review['review_text']); ?></p>
                            <p class="comment">Rating: <?php echo htmlspecialchars($review['review_rating']); ?>/5</p>
                            <p class="text-muted" style="font-size: 12px;"><?php echo date('F j, Y, g:i a', strtotime($review['review_date'])); ?></p>
                            
                            <?php
                            // Check if the logged-in user is the same as the reviewer
                            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['reviewer_id']):
                            ?>
                                <div class="reply-btn">
                                    <!-- Edit Button -->
                                    <div class="play-icon d-flex justify-content-start">
                                        <a class="popup-video btn btn-primary" href="edit_review.php?review_id=<?php echo $review['review_id']; ?>">Edit</a>
                                        <!-- Delete Button -->
                                        <a class="btn btn-danger ms-2" style="margin-left: 5px;" href="delete_review.php?review_id=<?php echo $review['review_id']; ?>" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                                    </div>               
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<!-- Reviews Section -->



<?php
include 'footer.php';
?>
