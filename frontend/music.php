<?php
session_start();
include 'header.php';

$letters = range('A', 'Z');

function getSongsByLetter($letter) {
    global $conn;
    $query = "SELECT a.audio_id, a.audio_name, c.category_artist
              FROM audio a
              JOIN categories c ON a.category_id = c.category_id
              WHERE a.audio_name LIKE '$letter%' 
              ORDER BY a.audio_name ASC";
    return mysqli_query($conn, $query);
}
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

<div class="container mt-5">
    <h4 class="text-center">Categories:</h4>
    
    <div class="row mb-4">
        <!-- Categories Text and Navigation Buttons (Centered) -->
        <div class="col-12 text-center">
            <a href="artist.php" class="btn btn-danger mx-2 px-3">Artist</a>
            <a href="album.php" class="btn btn-warning mx-2 px-3">Album</a>
            <a href="genre.php" class="btn btn-success mx-2 px-3">Genre</a>
            <a href="language.php" class="btn btn-primary mx-2 px-3">Language</a>
            <a href="year.php" class="btn btn-secondary mx-2 px-3">Year</a>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Search Form (Centered) -->
        <div class="col-12 text-center">
            <form method="POST" action="search_audio.php" class="d-flex justify-content-center">
                <div class="input-group" style="max-width: 400px;">
                    <input type="search" name="search_query" class="form-control form-control-sm" placeholder="Search" aria-label="Search">
                    <button class="btn btn-danger btn-sm" type="submit" style="margin-left: 10px;">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Line above Navigation Buttons -->
    <hr class="mb-4">

    <?php
    foreach ($letters as $letter) {
        $result = getSongsByLetter($letter);

        if (mysqli_num_rows($result) > 0) {
            echo "<h3 class='mb-3 text-center'>" . $letter . "</h3>";

            echo "<table class='table table-bordered' style='table-layout: fixed;'>
                    <thead class='bg-danger text-white'>
                        <tr>
                            <th>Song</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Display each song
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td> <a class='text-danger' href='detail.php?audio_id=" . htmlspecialchars($row['audio_id']) . "'> " . htmlspecialchars($row['audio_name']) . " (Click Here)</a></td>
                    </tr>";
            }

            echo "</tbody></table>";
        }
    }
    ?>
</div>

<?php
include 'footer.php';
?>
