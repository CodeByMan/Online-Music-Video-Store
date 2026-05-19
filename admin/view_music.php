<?php 
include('header.php');

$sql = "SELECT audio.*, categories.category_artist, categories.category_album, categories.category_genre, categories.category_language, categories.category_year, categories.category_image
        FROM audio
        INNER JOIN categories ON audio.category_id = categories.category_id
        ORDER BY audio.audio_id DESC";

$result = mysqli_query($conn, $sql);

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

<!-- audio Content -->
<div class="container mt-5">

    <?php
        if (mysqli_num_rows($result) > 0) {
            echo '<table class="table table-bordered">';
            echo '<thead class="bg-primary text-white">';
            echo '<tr>';
            echo '<th>Name</th>';
            echo '<th>Artist</th>';
            echo '<th>Audio</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = mysqli_fetch_assoc($result)) {
                $category_artist = htmlspecialchars($row['category_artist']);
                $audio_name = htmlspecialchars($row['audio_name']);
                
                echo '<tr>';
                echo '<td>' . $audio_name . '</td>';
                echo '<td>' . $category_artist . '</td>';
                echo '<td style="width: 300px;">';
                echo "<audio controls class='w-100'>
                        <source src='uploads/music/{$audio_name}' type='audio/mpeg'>
                        Your browser does not support the audio element.
                      </audio>";
                echo '</td>';
                echo '<td>';
                echo "<a href='edit_music.php?audio_id={$row['audio_id']}' class='btn btn-primary btn-sm'>Edit</a> ";
                echo "<a href='delete.php?audio_id={$row['audio_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this music file?\")'>Delete</a>";
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "<p>No music files found.</p>";
        }
    ?>
</div>
<!-- audion content -->

<?php
include 'footer.php';
?>
