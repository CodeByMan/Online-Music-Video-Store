<?php
session_start();
include 'header.php';
?>

    <!-- slider_area_start -->
    <div class="slider_area">
        <div class="single_slider d-flex align-items-center slider_bg_1">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="slider_text text-center">
                            <h3>Music</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- slider_area_end -->

    <!-- music_area  -->
    <div class="music_area">
        <div class="container">
        <?php 
            // Query to fetch the latest audio with its category details
            $sql = "SELECT audio.audio_name, categories.category_artist
                    FROM audio
                    INNER JOIN categories ON audio.category_id = categories.category_id
                    ORDER BY audio.audio_id DESC LIMIT 1"; // Get the last added audio

            $result = mysqli_query($conn, $sql);

            // Check if the query was successful and fetch the row
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $audio_name = $row['audio_name'];
                $category_artist = $row['category_artist'];
            } else {
                echo "No music found.";
                exit;
            }
            ?>
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-10">
                    <div class="row align-items-center">
                        <div class="col-xl-12 col-md-12">
                            <div class="music_field">
                                <div class="thumb">
                                <img src="img/banner/trend.jpg" alt="" style="max-width: 150px; height: auto;">
                                </div>
                                <div class="audio_name">
                                    <div class="name">
                                        <span class="bg-danger text-white px-2">Trending #1</span>
                                        <h4 class="mt-2"><?php echo $audio_name; ?> (<?php echo $category_artist; ?>)</h4>
                                    </div>
                                    <audio preload="auto" controls>
                                        <source src="../admin/uploads/music/<?php echo $audio_name; ?>" type="audio/mp3">
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- music_area end  -->

    <!-- about_area  -->
    <div class="about_area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-5 col-md-6">
                    <div class="about_thumb">
                        <img class="img-fluid" src="img/banner/e5.jpg" alt="">
                    </div>
                </div>
                <div class="col-xl-7 col-md-6">
                    <div class="about_info">
                        <h3>Music Magic</h3>
                        <p>Music is like a magical sound party that can make you dance, sing, and feel all kinds of emotions! It’s made by mixing beats, tunes, and rhythms together to create something that makes you tap your feet or get lost in the moment. Whether it's your favorite song on the radio or a random melody you hum, music is everywhere, ready to make any moment more fun and full of life. It's the universal language that everyone can groove to, no matter where you're from or how old you are! 🎶💃🎸</p>
                        <div class="signature">
                            <img src="img/about/signature.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ about_area  -->

    <hr>

    <!-- music_area -->
    <div class="music_area music_gallery">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-heading style-2">
                        <p>See what’s new</p>
                        <h2>Latest Music</h2>
                    </div>
                </div>
            </div>

            <?php
            $query = "SELECT audio.audio_name, categories.category_image, categories.category_artist 
                    FROM audio 
                    JOIN categories ON audio.category_id = categories.category_id 
                    ORDER BY audio.audio_id DESC LIMIT 4";

            $result = mysqli_query($conn, $query);

            if (!$result) {
                echo "Error: " . mysqli_error($conn);
                exit();
            }

            if (mysqli_num_rows($result) > 0) {
                // Loop through each track
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="row align-items-center justify-content-center mb-20">
                <div class="col-xl-10">
                    <div class="row align-items-center">
                        <div class="col-xl-12 col-md-12">
                            <div class="music_field">
                                <div class="thumb">
                                    <!-- Display audio image -->
                                    <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 150px; height: auto;">
                                </div>
                                <div class="audio_name">
                                    <div class="name">
                                        <span class="bg-danger text-white px-2">New</span>
                                        <h4><?php echo $row['audio_name']; ?></h4>
                                        <h6><?php echo $row['category_artist']; ?></h6>
                                    </div>
                                    <audio preload="auto" controls style="width: 100%; max-width: 350px;">
                                        <!-- Adjusted to fetch the correct audio file -->
                                        <source src="../admin/uploads/music/<?php echo $row['audio_name']; ?>" type="audio/mp3">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p>No tracks found</p>";
            }
            ?>

            <div class="row">
                <div class="col-12">
                    <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                        <a href="music.php" class="btn oneMusic-btn">See More Music <i class="fa fa-angle-double-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- music_area end -->

    <!-- ##### Album Area Start ##### -->
    <section class="oneMusic-buy-now-area has-fluid section-padding-100">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-heading style-2">
                        <p>See what’s new</p>
                        <h2>Latest Albums</h2>
                    </div>
                </div>
            </div>

            <div class="row">
            <?php
            $query = "SELECT category_artist, category_album, category_image, category_id 
                    FROM categories 
                    ORDER BY category_id DESC LIMIT 6";
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

            <div class="row">
                <div class="col-12">
                    <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                        <a href="album.php" class="btn oneMusic-btn">See More Album <i class="fa fa-angle-double-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Album Area End ##### -->

    <hr>

    <!-- ##### Video Area Start ##### -->
    <section class="oneMusic-buy-now-area has-fluid section-padding-100">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-heading style-2">
                        <p>See what’s new</p>
                        <h2>Latest Videos</h2>
                    </div>
                </div>
            </div>

            <div class="row">
            <?php

            $query = "SELECT v.video_name, c.category_artist, c.category_image
                    FROM videos v
                    JOIN categories c ON v.category_id = c.category_id
                    ORDER BY v.video_id DESC LIMIT 6";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
            
                <!-- Single Video Area -->
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                        <div class="album-thumb">
                            <!-- Video Image -->
                            <img src="../admin/uploads/images/<?php echo $row['category_image']; ?>" alt="" style="max-width: 100%;">
                            <!-- Album Price -->
                            <div class="album-price">
                                <p class="bg-danger text-white">New</p>
                            </div>
                            <!-- Play Icon -->
                            <div class="play-icon">
                                <a class="popup-video" href="../admin/uploads/videos/<?php echo $row['video_name']; ?>"> 
                                    <span class="fa fa-play"></span>
                                </a>
                            </div>
                        </div>
                        <div class="album-info">
                            <a href="#">
                                <h5><?php echo $row['category_artist']; ?></h5>
                            </a>
                            <p><?php echo $row['video_name']; ?></p>
                        </div>
                    </div>
                </div>
                
            <?php
                }
            } else {
                echo "<p>No videos found.</p>";
            }
            ?>
            </div>


            <div class="row">
                <div class="col-12">
                    <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                        <a href="video.php" class="btn oneMusic-btn">See More Videos <i class="fa fa-angle-double-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Video Area End ##### -->

    <hr>

    <!-- ##### Miscellaneous Area Start ##### -->
    <section class="miscellaneous-area section-padding-100-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-heading style-2">
                        <p>See what’s new</p>
                        <h2>Collections</h2>
                    </div>
                </div>
            </div>

            <div class="row">

            <!-- ***** Weeks Top ***** -->
            <div class="col-12 col-lg-4">
                <div class="weeks-top-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>Top Albums </h2>
                    </div>
                    <?php
                    $query = "SELECT category_id, category_artist, category_image, category_genre FROM categories ORDER BY category_id ASC LIMIT 5";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                ?>
        
                <!-- Single Top Item -->
                <div class="single-top-item d-flex wow fadeInUp" data-wow-delay="350ms">
                    <div class="thumbnail">
                        <a href="album_music.php?album_id=<?php echo $row['category_id']; ?>">
                            <img src="../admin/uploads/images/<?php echo htmlspecialchars($row['category_image']); ?>" alt="Category Image">
                        </a>
                    </div>
                    <div class="content-">
                        <a href="album_music.php?album_id=<?php echo $row['category_id']; ?>">
                            <h6><?php echo htmlspecialchars($row['category_artist']); ?></h6>
                            <p><?php echo htmlspecialchars($row['category_genre']); ?></p>
                            <span class="badge custom-badge bg-danger text-white">New</span>
                        </a>
                    </div>
                </div>
                
                <?php
                    }
                } else {
                    echo '<p>No top albums available.</p>';
                }
                ?>
                
                </div>
            </div>

            <!-- ***** New Hits Songs ***** -->
            <div class="col-12 col-lg-4">
                <div class="new-hits-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>New Hits</h2>
                    </div>
                    <?php
                    $query = "SELECT audio.audio_name, categories.category_image, categories.category_artist 
                            FROM audio 
                            JOIN categories ON audio.category_id = categories.category_id 
                            ORDER BY audio.audio_id DESC LIMIT 4";  // Fetch last 5 tracks
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="single-new-item d-flex align-items-center justify-content-between wow fadeInUp" data-wow-delay="100ms">
                        <div class="first-part d-flex align-items-center">
                            <div class="thumbnail">
                                <span class="badge custom-badge bg-danger text-white">New</span>
                                <img src="../admin/uploads/images/<?php echo htmlspecialchars($row['category_image']); ?>" alt="Category Image">
                            </div>
                            <div class="content-">
                                <h6><?php echo htmlspecialchars($row['category_artist']); ?></h6>
                                <p><?php echo htmlspecialchars($row['audio_name']); ?></p>
                            </div>
                        </div>
                            <audio preload="auto" controls>
                                <source src="../admin/uploads/music/<?php echo htmlspecialchars($row['audio_name']); ?>">
                            </audio>
                    </div>
                <?php
                        }
                    } else {
                        echo '<p>No new hits available.</p>';
                    }
                ?>
                </div>
            </div>

            <!-- ***** Popular Artists ***** -->
            <div class="col-12 col-lg-4">
                <div class="popular-artists-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>Popular Artist</h2>
                    </div>

                    <?php
                    $query = "SELECT category_artist, category_image FROM categories LIMIT 6";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <!-- Single Artist -->
                        <div class="single-artists d-flex align-items-center wow fadeInUp" data-wow-delay="50ms">
                            <div class="thumbnail">
                                <img src="../admin/uploads/images/<?php echo htmlspecialchars($row['category_image']); ?>" alt="Artist Image">
                            </div>
                            <div class="content-">
                                <a href="artist.php"><?php echo htmlspecialchars($row['category_artist']); ?></a>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        echo '<p>No popular artists available.</p>';
                    }
                    ?>
                    
                </div>
            </div>

            </div>
        </div>
    </section>
    <!-- ##### Miscellaneous Area End ##### -->

    <hr>

    <!-- gallery -->
    <div class="gallery_area">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-heading style-2">
                        <p>See what’s new</p>
                        <h2>Photos</h2>
                    </div>
                </div>
            </div>
            <div class="row grid">
                <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/1.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/1.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/12.jpg" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/12.jpg" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/2.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/2.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/3.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/3.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/4.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/4.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/5.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/5.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/6.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/6.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/7.png" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/7.png" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                        <div class="single-gallery mb-30">
                            <div class="portfolio-img">
                                    <img src="img/gallery/9.jpg" alt="">
                            </div>
                            <div class="gallery_hover">
                                    <a class="popup-image" href="img/gallery/9.jpg" class="hover_inner">
                                        <i class="ti-plus"></i>
                                    </a>
                                </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <!--/ gallery -->

    <!-- contact_rsvp -->
    <div class="contact_rsvp">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="text text-center">
                        <h3>Send us your query anytime!</h3>
                        <a href="contact.php" class="btn oneMusic-btn">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ contact_rsvp -->

    


    <?php
    include 'footer.php';
    ?>

    <script>
        $(function() {
        $('audio').audioPlayer({

        });
        });
    </script>