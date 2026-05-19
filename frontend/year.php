<?php
session_start();
include 'header.php';

function getSongsByYear($year) {
    global $conn;
    $query = "SELECT a.audio_id, a.audio_name, c.category_artist
              FROM audio a
              JOIN categories c ON a.category_id = c.category_id
              WHERE c.category_year = '$year' 
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
                    <h3>Songs by Year</h3>
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
            <a href="music.php" class="btn btn-danger mx-2 px-3">All</a>
            <a href="artist.php" class="btn btn-warning mx-2 px-3">Artist</a>
            <a href="album.php" class="btn btn-success mx-2 px-3">Album</a>
            <a href="genre.php" class="btn btn-primary mx-2 px-3">Genre</a>
            <a href="language.php" class="btn btn-secondary mx-2 px-3">Language</a>
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
    // Fetch distinct years from the categories table
    $result_years = mysqli_query($conn, "SELECT DISTINCT category_year FROM categories ORDER BY category_year DESC");

    if ($result_years) {
        if (mysqli_num_rows($result_years) > 0) {
            // Loop through each year
            while ($row_year = mysqli_fetch_assoc($result_years)) {
                $year = $row_year['category_year'];

                // Fetch songs for the current year
                $result_songs = getSongsByYear($year);

                if ($result_songs && mysqli_num_rows($result_songs) > 0) {
                    echo "<h3 class='mb-3 text-center'>" . $year . "</h3>";

                    echo "<table class='table table-bordered' style='table-layout: fixed;'>
                            <thead class='bg-danger text-white'>
                                <tr>
                                    <th>Song</th>
                                </tr>
                            </thead>
                            <tbody>";

                    // Display each song
                    while ($row_song = mysqli_fetch_assoc($result_songs)) {
                        echo "<tr>
                                <td> <a class='text-danger' href='detail.php?audio_id=" . htmlspecialchars($row_song['audio_id']) . "'> " . htmlspecialchars($row_song['audio_name']) . " (Click Here)</a></td>
                            </tr>";
                    }

                    echo "</tbody></table>";
                }
            }
        } else {
            echo "<p>No songs found by year.</p>";
        }
    } else {
        echo "<p>Error fetching years: " . mysqli_error($conn) . "</p>";
    }
    ?>
</div>

<?php
include 'footer.php';
?>
