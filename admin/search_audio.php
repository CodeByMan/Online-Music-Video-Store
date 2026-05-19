<?php 
include('header.php');

$search_query = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
}

$sql_audio = "
    SELECT audio.audio_name, audio.category_id, 
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

<!-- Search bar -->
<form method="POST" action="search_audio.php" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
    <div class="input-group">
        <input type="search" name="search_query" class="form-control bg-light border-0 small" placeholder="Search for audio..." aria-label="Search" aria-describedby="basic-addon2">
        <div class="input-group-append">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
    </div>
</form>
<!-- search bar -->

<!-- audio content -->
<div class="container mt-5">
    <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>" - Audio</h5>

    <?php if (mysqli_num_rows($result_audio) > 0): ?>
        <table class="table table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th scope="col">Poster</th>
                    <th scope="col">Song</th>
                    <th scope="col">Artist</th>
                    <th scope="col">Album</th>
                    <th scope="col">Genre</th>
                    <th scope="col">Language</th>
                    <th scope="col">Year</th>
                    <th scope="col">Audio Preview</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_audio)): ?>
                    <tr>
                        <td><img src="uploads/images/<?php echo htmlspecialchars($row['category_image']); ?>" class="img-thumbnail" alt="Music Thumbnail" style="max-width: 100px; height: auto;"></td>
                        <td><?php echo htmlspecialchars($row['audio_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_artist']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_album']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_genre']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_language']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_year']); ?></td>
                        <td>
                            <audio controls>
                                <source src="uploads/music/<?php echo htmlspecialchars($row['audio_name']); ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No audio files found.</p>
    <?php endif; ?>
</div>
<!-- audion content -->

<?php
include 'footer.php';
?>
