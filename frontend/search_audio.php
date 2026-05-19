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
                    <h3>Songs</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<section>
<div class="container mt-5">
    <h4 class="text-center">Search Albums By Categories:</h4>
        <p class="text-center">(Artist - Genre - Language - Year)</p>
            <!-- Search Form (Centered) -->
            <div class="col-12 text-center">
                <form method="POST" action="search_audio.php" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="search" name="search_query" class="form-control form-control-sm" placeholder="Search" aria-label="Search">
                        <button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button>
                    </div>
                </form>
            </div>

    <!-- Line above Navigation Buttons -->
    <hr class="mb-4">

<!-- audio content -->
    <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - Audio</h5>

    <!-- Display audio results in table format -->
    <?php if (mysqli_num_rows($result_audio) > 0): ?>
        <table class='table table-bordered' style='table-layout: fixed;'>
            <thead class='bg-danger text-white'>
                <tr>
                    <th style="width: 30%;">Song</th>
                    <th style="width: 20%;">Artist</th>
                    <th style="width: 20%;">Album</th>
                    <th style="width: 15%;">Genre</th>
                    <th style="width: 15%;">Year</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result_audio)) {
                ?>
                <tr>
                    <td><a class="text-danger" href="detail.php?audio_id=<?php echo htmlspecialchars($row['audio_id']); ?>"><?php echo htmlspecialchars($row['audio_name']); ?> (Click Here)</a></td>
                    <td><strong><?php echo htmlspecialchars($row['category_artist']); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($row['category_album']); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($row['category_genre']); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($row['category_year']); ?></strong></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No audio files found.</p>
    <?php endif; ?>
    <div class="d-flex justify-content-center">
            <a href="music.php" class="btn oneMusic-btn m-5">Back To All Music</a>
    </div>
</div>
</section>
<!-- audio content -->

<?php
include 'footer.php';
?>
