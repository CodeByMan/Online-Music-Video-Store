<?php  
include('header.php');

$sql = "SELECT videos.*, categories.category_artist
        FROM videos
        INNER JOIN categories ON videos.category_id = categories.category_id
        ORDER BY videos.video_id DESC";

$result = mysqli_query($conn, $sql);
?>

<!-- Search bar -->
<form method="POST" action="search_video.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group">
        <input type="search" name="search_query" class="form-control bg-light border-0 small" placeholder="Search for video..." aria-label="Search" aria-describedby="basic-addon2">
        <div class="input-group-append">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
    </div>
</form>
<!-- Search bar -->

<!-- Video Content -->
<div class="container mt-5">

    <div class="row">
    <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='col-md-4 mb-4'>
                        <div class='card d-flex flex-column'>
                            <div class='video-container' style='flex-grow: 1;'>
                                <!-- Set a fixed height for the video and ensure it doesn't stretch -->
                                <video controls class='card-img-top' style='width: 100%; height: 300px;'>
                                    <source src='uploads/videos/{$row['video_name']}' type='video/mp4'>
                                    Your browser does not support the video element.
                                </video>
                            </div>
                            <div class='card-body'>
                                <h6 class='card-title'>{$row['video_name']}</h6>
                                <p class='card-text'>
                                    {$row['category_artist']}<br>
                                </p>
                                <div class='mt-2'>
                                    <a href='edit_video.php?video_id={$row['video_id']}' type='button' class='btn btn-primary'>Edit</a>
                                    <a href='delete.php?video_id={$row['video_id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this video?\")'>Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>";
            }
        } else {
            echo "<p class='col-12'>No videos found.</p>";
        }
    ?>

    </div>
</div>
<!-- Video Content -->

<?php
include 'footer.php';
?>
