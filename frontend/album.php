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
                    <h3>Songs by Album</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ bradcam_area -->

<!-- ##### Album Area Start ##### -->
<section class="oneMusic-buy-now-area has-fluid">
<div class="container mt-5">
    <h4 class="text-center">Categories:</h4>
    
    <div class="row mb-4">
        <!-- Categories Text and Navigation Buttons (Centered) -->
        <div class="col-12 text-center">
            <a href="music.php" class="btn btn-danger mx-2 px-3">All</a>
            <a href="artist.php" class="btn btn-warning mx-2 px-3">Artist</a>
            <a href="genre.php" class="btn btn-success mx-2 px-3">Genre</a>
            <a href="language.php" class="btn btn-primary mx-2 px-3">Language</a>
            <a href="year.php" class="btn btn-secondary mx-2 px-3">Year</a>
        </div>
    </div>

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


        <div class="row mt-5">
        <?php
        $query = "SELECT category_artist, category_album, category_image, category_id 
                  FROM categories 
                  ORDER BY category_id ASC";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            // Loop through the results and display each album
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        
            <!-- Single Album Area -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                    <!-- Update the link to point to album_music.php and pass the category_album as a parameter -->
                    <a href="album_music.php?album_id=<?php echo $row['category_id']; ?>">
                        <div class="album-thumb">
                            <!-- Album Image -->
                            <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 100%;">
                        </div>
                        <div class="album-info">
                            <!-- Album Name -->
                            <h5><?php echo $row['category_artist']; ?></h5>
                            <!-- Album Artist -->
                            <p><?php echo $row['category_album']; ?></p>
                        </div>
                    </a>
                </div>
            </div>
            
        <?php
            }
        } else {
            echo "<p>No albums found.</p>";
        }
        ?>
        </div>
    </div>
</section>
<!-- ##### Album Area End ##### -->

<?php
include 'footer.php';
?>
