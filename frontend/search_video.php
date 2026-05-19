<?php
session_start();
include 'header.php';

$search_query = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search_query'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_query']);
}

$sql_video = "
    SELECT videos.video_name, videos.category_id, 
           categories.category_artist, categories.category_album, categories.category_genre, categories.category_language, categories.category_year, categories.category_image
    FROM videos 
    INNER JOIN categories ON videos.category_id = categories.category_id
    WHERE videos.video_name LIKE '%$search_query%' 
    OR categories.category_artist LIKE '%$search_query%' 
    OR categories.category_album LIKE '%$search_query%' 
    OR categories.category_genre LIKE '%$search_query%' 
    OR categories.category_language LIKE '%$search_query%' 
    OR categories.category_year LIKE '%$search_query%';
";

$result_video = mysqli_query($conn, $sql_video);
?>

<!-- bradcam_area -->
<div class="bradcam_area breadcam_bg_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text text-center">
                    <h3>Videos</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<div class="container mt-5">
    <h4 class="text-center">Search Videos By Categories:</h4>
    <p class="text-center">(Artist - Genre - Language - Year)</p>
        <!-- Search Form (Centered) -->
        <div class="col-12 text-center">
            <form method="POST" action="search_video.php" class="d-flex justify-content-center">
                <div class="input-group" style="max-width: 400px;">
                    <input type="search" name="search_query" class="form-control form-control-sm" placeholder="Search" aria-label="Search">
                    <button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button>
                </div>
            </form>
        </div>

    <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - Video</h5>

    <!-- Display video results -->
    <?php if ($result_video && mysqli_num_rows($result_video) > 0): ?>
        <div class="row mt-4">
            <?php while ($row = mysqli_fetch_assoc($result_video)): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                            <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                                <div class="album-thumb">
                                    <!-- Video Image -->
                                    <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 100%;">
                                    <!-- Play Icon -->
                                    <div class="play-icon">
                                        <a class="popup-video" href="../admin/uploads/videos/<?php echo $row['video_name']; ?>"> 
                                            <span class="fa fa-play"></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="album-info">
                                    <a href="#">
                                        <h5><?php echo $row['video_name']; ?></h5>
                                    </a>
                                    <p><?php echo $row['category_artist']; ?></p>
                                </div>
                            </div>
                        </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No video files found.</p>
    <?php endif; ?>
    <div class="d-flex justify-content-center">
            <a href="video.php" class="btn oneMusic-btn m-5">Back To All Videos</a>
    </div>
</div>
<!-- video content -->

<?php
include 'footer.php';
?>
