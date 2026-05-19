<?php
session_start();
include 'header.php';

// Check if album_id is passed via URL
if (isset($_GET['album_id'])) {
    $album_id = $_GET['album_id'];

    // Query to fetch album details
    $album_query = "SELECT category_album, category_artist, category_year, category_language, category_image, category_genre 
                    FROM categories WHERE category_id = '$album_id'";
    $album_result = mysqli_query($conn, $album_query);
    $album = mysqli_fetch_assoc($album_result);

    // Query to fetch all songs for this album
    $songs_query = "SELECT audio_id, audio_name 
                    FROM audio 
                    WHERE category_id = '$album_id'
                    ORDER BY audio_name ASC";
    $songs_result = mysqli_query($conn, $songs_query);
}
?>

<!-- bradcam_area -->
<div class="bradcam_area breadcam_bg_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text text-center">
                    <h3>Album Songs</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<!-- ##### Album Music Area Start ##### -->
<section class="oneMusic-buy-now-area has-fluid section-padding-100">
    <div class="container">
        <?php if (isset($album)): ?>
            <!-- Album Details Card -->
            <div class="row mb-4">
                <div class="card mb-3 border-0" style="max-width: 100%">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="../admin/uploads/images/<?php echo htmlspecialchars($album['category_image']); ?>" 
                            style="width: 700px" 
                                 alt="<?php echo htmlspecialchars($album['category_album']); ?>">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body" style="font-size: 1.2rem;">
                                <h5 class="card-title" style="font-size: 1.5rem;"><?php echo htmlspecialchars($album['category_album']); ?></h5>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Artist:</strong> <?php echo htmlspecialchars($album['category_artist']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Genre:</strong> <?php echo htmlspecialchars($album['category_genre']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Language:</strong> <?php echo htmlspecialchars($album['category_language']); ?></p>
                                <p class="card-text" style="font-size: 1.2rem;"><strong>Year:</strong> <?php echo htmlspecialchars($album['category_year']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Song List Section -->
            <div class="row mb-4">
                <!-- Table to display songs -->
                <div class="table-responsive mt-4">
                    <?php
                    if (mysqli_num_rows($songs_result) > 0) {
                        echo "<table class='table table-bordered' style='table-layout: fixed;'>
                                <thead class='bg-danger text-white'>
                                    <tr>
                                        <th style='width: 40%;'>Song</th>
                                        <th style='width: 60%;'>Player</th>
                                    </tr>
                                </thead>
                                <tbody>";

                        // Loop through each song and display it
                        while ($song = mysqli_fetch_assoc($songs_result)) {
                            // Fetch category info for the artist and genre
                            $song_info_query = "SELECT category_artist, category_genre 
                                                FROM categories 
                                                WHERE category_id = '$album_id'";
                            $song_info_result = mysqli_query($conn, $song_info_query);
                            $song_info = mysqli_fetch_assoc($song_info_result);

                            echo "<tr>
                                    <td><strong style='font-size: 1.2rem;'>" . htmlspecialchars($song['audio_name']) . "</strong></td>
                                    <td>
                                        <audio controls style='width: 100%;'>
                                            <source src='../admin/uploads/music/" . htmlspecialchars($song['audio_name']) . "' type='audio/mp3'>
                                            Your browser does not support the audio element.
                                        </audio>
                                    </td>
                                  </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<p>No songs available for this album.</p>";
                    }
                    ?>
                </div>
            </div>
        <?php else: ?>
            <p>Album not found.</p>
        <?php endif; ?>
        <div class="d-flex justify-content-center">
            <a href="search_album.php" class="btn oneMusic-btn">Search More Albums</a>
        </div>
    </div>
</section>
<!-- ##### Album Music Area End ##### -->

<?php
include 'footer.php';
?>
