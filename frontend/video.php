<?php
session_start();
include 'header.php';
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

<!-- ##### Video Area Start ##### -->
<section class="oneMusic-buy-now-area has-fluid mt-5">
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

        <div class="row mt-5">
            <?php
            $videoFound = false; // Flag to check if any videos are found

            // Directly fetching all videos from the database
            $query = "SELECT v.video_id, v.video_name, c.category_artist, c.category_image
                      FROM videos v
                      JOIN categories c ON v.category_id = c.category_id
                      ORDER BY v.video_name ASC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $videoFound = true;

                // Display each video as a card
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                            <div class="album-thumb">
                                <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 100%;">
                                <div class="play-icon">
                                    <a class="popup-video" href="../admin/uploads/videos/<?php echo $row['video_name']; ?>"> 
                                        <span class="fa fa-play"></span>
                                    </a>
                                </div>
                            </div>
                            <div class="album-info">
                                <h5><?php echo $row['video_name']; ?></h5>
                                <p><?php echo $row['category_artist']; ?></p>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            
            if (!$videoFound) {
                echo "<p>No video files found.</p>";
            }
            ?>
        </div>
    </div>
</section>
<!-- ##### Video Area End ##### -->

<?php include 'footer.php'; ?>
