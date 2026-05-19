<?php 
include('header.php');

$search_query = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
}

$sql_video = "
    SELECT videos.video_name, videos.category_id, categories.category_artist, categories.category_album, categories.category_genre, categories.category_language, categories.category_year, videos.video_id
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

<!-- video content -->
<div class="container mt-5">
    <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - Video</h5>

    <!-- Display video results -->
    <?php if (mysqli_num_rows($result_video) > 0): ?>
        <div class="row mt-4">
            <?php while ($row = mysqli_fetch_assoc($result_video)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card d-flex flex-column">
                        <div class="video-container" style="flex-grow: 1;">
                            <!-- Set a fixed height for the video and ensure it doesn't stretch -->
                            <video controls class="card-img-top" style="width: 100%; height: 300px;">
                                <source src="uploads/videos/<?php echo htmlspecialchars($row['video_name']); ?>" type="video/mp4">
                                Your browser does not support the video element.
                            </video>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($row['video_name']); ?></h6>
                            <p class="card-text">
                                <?php echo htmlspecialchars($row['category_artist']); ?><br>
                            </p>
                            <div class='mt-2'>
                                <!-- Properly escape the video_id for URL -->
                                <a href="edit_video.php?video_id=<?php echo htmlspecialchars($row['video_id']); ?>" type="button" class="btn btn-primary">Edit</a>
                                <a href="delete.php?video_id=<?php echo htmlspecialchars($row['video_id']); ?>" class="btn btn-danger" onclick='return confirm("Are you sure you want to delete this video?")'>Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No video files found.</p>
    <?php endif; ?>
</div>
<!-- video content -->

<?php
include 'footer.php';
?>
