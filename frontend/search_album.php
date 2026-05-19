<?php
session_start();
include 'header.php';

$search_query = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search_query'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_query']);
}

$sql_audio = "
    SELECT audio.audio_id, audio.audio_name, audio.category_id, 
           categories.category_artist, categories.category_album, categories.category_genre, categories.category_language, categories.category_year, categories.category_image
    FROM audio 
    INNER JOIN categories ON audio.category_id = categories.category_id
    WHERE audio.audio_name LIKE '%$search_query%' 
    OR categories.category_artist LIKE '%$search_query%' 
    OR categories.category_album LIKE '%$search_query%' 
    OR categories.category_genre LIKE '%$search_query%' 
    OR categories.category_language LIKE '%$search_query%' 
    OR categories.category_year LIKE '%$search_query%';
";

$result_audio = mysqli_query($conn, $sql_audio);
?>

<!-- bradcam_area -->
<div class="bradcam_area breadcam_bg_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="bradcam_text text-center">
                    <h3>Album</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<!-- ##### Search Results Area Start ##### -->
<section class="oneMusic-buy-now-area has-fluid section-padding-100">
    <div class="container">
        <h4 class="text-center">Search Albums By Categories:</h4>
        <p class="text-center">(Artist - Genre - Language - Year)</p>

        <div class="row mb-4">
            <!-- Search Form (Centered) -->
            <div class="col-12 text-center">
                <form method="POST" action="search_album.php" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="search" name="search_query" class="form-control form-control-sm" placeholder="Search" aria-label="Search">
                        <button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Line above Navigation Buttons -->
        <hr class="mb-4">

        <!-- Display search query result header -->
        <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h5>

        <!-- Display audio results in grid format -->
        <div class="row mt-5">
        <?php if (mysqli_num_rows($result_audio) > 0): ?>
            <?php
            while ($row = mysqli_fetch_assoc($result_audio)) {
            ?>
            <!-- Single Audio Album -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                    <!-- Update the link to point to album_music.php and pass the category_album as a parameter -->
                    <a href="album_music.php?album_id=<?php echo $row['category_id']; ?>">
                        <div class="album-thumb">
                            <!-- Album Image -->
                            <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 100%;">
                        </div>
                        <div class="album-info">
                            <h5><?php echo $row['category_artist']; ?></h5>
                            <p><?php echo $row['category_album']; ?></p>
                        </div>
                    </a>
                </div>
            </div>
            <?php
            }
            ?>
        <?php else: ?>
            <p class="text-center">No albums found matching your search.</p>
        <?php endif; ?>
    </div>
        <div class="d-flex justify-content-center">
            <a href="album.php" class="btn oneMusic-btn">Back To Albums</a>
        </div>
    </div>
</section>
<!-- ##### Search Results Area End ##### -->

<?php
include 'footer.php';
?>
