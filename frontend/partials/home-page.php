<?php
$latestAudio = db_fetch_one(
    $conn,
    'SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_artist
     FROM audio a
     INNER JOIN categories c ON a.category_id = c.category_id
     ORDER BY a.audio_id DESC
     LIMIT 1'
);

$latestMusic = db_fetch_all(
    $conn,
    'SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_image, c.category_artist
     FROM audio a
     INNER JOIN categories c ON a.category_id = c.category_id
     ORDER BY a.audio_id DESC
     LIMIT 4'
);

$latestAlbums = db_fetch_all(
    $conn,
    'SELECT category_id, category_artist, category_album, category_image
     FROM categories
     ORDER BY category_id DESC
     LIMIT 6'
);

$latestVideos = db_fetch_all(
    $conn,
    'SELECT v.video_id, v.video_title, v.video_mime_type, c.category_artist, c.category_image
     FROM videos v
     INNER JOIN categories c ON v.category_id = c.category_id
     ORDER BY v.video_id DESC
     LIMIT 6'
);

$topAlbums = db_fetch_all(
    $conn,
    'SELECT category_id, category_artist, category_genre, category_image
     FROM categories
     ORDER BY category_id ASC
     LIMIT 5'
);

$newHits = db_fetch_all(
    $conn,
    'SELECT a.audio_id, a.audio_title, a.audio_mime_type, c.category_artist, c.category_image
     FROM audio a
     INNER JOIN categories c ON a.category_id = c.category_id
     ORDER BY a.audio_id DESC
     LIMIT 4'
);

$popularArtists = db_fetch_all(
    $conn,
    'SELECT category_artist, MIN(category_image) AS category_image
     FROM categories
     GROUP BY category_artist
     ORDER BY MIN(category_id) ASC
     LIMIT 6'
);

$musicMoreUrl = $homeAuthenticated ? 'music.php' : 'signin.php';
$musicMoreText = $homeAuthenticated ? 'See More Music' : 'Login For More';
$albumMoreUrl = $homeAuthenticated ? 'album.php' : 'signin.php';
$albumMoreText = $homeAuthenticated ? 'See More Album' : 'Login For More';
$videoMoreUrl = $homeAuthenticated ? 'video.php' : 'signin.php';
$videoMoreText = $homeAuthenticated ? 'See More Videos' : 'Login For More';
$contactUrl = $homeAuthenticated ? 'contact.php' : 'signin.php';
$contactText = $homeAuthenticated ? 'Contact Us' : 'Login To Contact Us';
$previewParameter = $homeAuthenticated ? '' : '&amp;preview=1';

if ($homeAuthenticated) {
    include dirname(__DIR__) . '/header.php';
} else {
    include __DIR__ . '/public-header.php';
}
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
        <?php if ($latestAudio !== null): ?>
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
                                        <h4 class="mt-2"><?php echo e($latestAudio['audio_title']); ?> (<?php echo e($latestAudio['category_artist']); ?>)</h4>
                                    </div>
                                    <audio preload="auto" controls>
                                        <source src="media.php?type=audio&amp;id=<?php echo (int) $latestAudio['audio_id']; ?><?php echo $previewParameter; ?>" type="<?php echo e($latestAudio['audio_mime_type'] ?: 'audio/mpeg'); ?>">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>No music found.</p>
        <?php endif; ?>
    </div>
</div>
<!-- music_area end  -->

<!-- about_area -->
<div class="about_area">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-5 col-md-6">
                <div class="about_thumb">
                    <img class="img-fluid" src="img/banner/e5.jpg" alt="Music performance">
                </div>
            </div>
            <div class="col-xl-7 col-md-6">
                <div class="about_info">
                    <h3>Music Magic</h3>
                    <p>Music is like a magical sound party that can make you dance, sing, and feel all kinds of emotions! It’s made by mixing beats, tunes, and rhythms together to create something that makes you tap your feet or get lost in the moment. Whether it's your favorite song on the radio or a random melody you hum, music is everywhere, ready to make any moment more fun and full of life. It's the universal language that everyone can groove to, no matter where you're from or how old you are! 🎶💃🎸</p>
                    <div class="signature">
                        <img src="img/about/signature.png" alt="One Sound signature">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ about_area -->

<hr>

<!-- music_area -->
<div id="music" class="music_area music_gallery">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading style-2">
                    <p>See what’s new</p>
                    <h2>Latest Music</h2>
                </div>
            </div>
        </div>

        <?php if ($latestMusic !== []): ?>
            <?php foreach ($latestMusic as $row): ?>
                <div class="row align-items-center justify-content-center mb-20">
                    <div class="col-xl-10">
                        <div class="row align-items-center">
                            <div class="col-xl-12 col-md-12">
                                <div class="music_field">
                                    <div class="thumb">
                                        <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['audio_title']); ?>" style="max-width: 150px; height: auto;">
                                    </div>
                                    <div class="audio_name">
                                        <div class="name">
                                            <span class="bg-danger text-white px-2">New</span>
                                            <h4><?php echo e($row['audio_title']); ?></h4>
                                            <h6><?php echo e($row['category_artist']); ?></h6>
                                        </div>
                                        <audio preload="auto" controls style="width: 100%; max-width: 350px;">
                                            <source src="media.php?type=audio&amp;id=<?php echo (int) $row['audio_id']; ?><?php echo $previewParameter; ?>" type="<?php echo e($row['audio_mime_type'] ?: 'audio/mpeg'); ?>">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No tracks found.</p>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                    <a href="<?php echo e($musicMoreUrl); ?>" class="btn oneMusic-btn"><?php echo e($musicMoreText); ?> <i class="fa fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- music_area end -->

<hr>

<!-- Album Area Start -->
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
            <?php if ($latestAlbums !== []): ?>
                <?php foreach ($latestAlbums as $row): ?>
                    <?php $albumUrl = $homeAuthenticated ? 'album_music.php?album_id=' . (int) $row['category_id'] : 'signin.php'; ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                            <a href="<?php echo e($albumUrl); ?>">
                                <div class="album-thumb">
                                    <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['category_album']); ?>" style="max-width: 100%;">
                                    <?php if (!$homeAuthenticated): ?>
                                        <div class="album-price">
                                            <p class="bg-success text-white">Pro</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="album-info">
                                    <h5><?php echo e($row['category_artist']); ?></h5>
                                    <p><?php echo e($row['category_album']); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No albums found.</p>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                    <a href="<?php echo e($albumMoreUrl); ?>" class="btn oneMusic-btn"><?php echo e($albumMoreText); ?> <i class="fa fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Album Area End -->

<hr>

<!-- Video Area Start -->
<section id="videos" class="oneMusic-buy-now-area has-fluid section-padding-100">
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
            <?php if ($latestVideos !== []): ?>
                <?php foreach ($latestVideos as $row): ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <div class="single-album-area wow fadeInUp" data-wow-delay="100ms">
                            <div class="album-thumb">
                                <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['video_title']); ?>" style="max-width: 100%;">
                                <div class="album-price">
                                    <p class="bg-danger text-white">New</p>
                                </div>
                                <div class="play-icon">
                                    <a class="popup-video" href="media.php?type=video&amp;id=<?php echo (int) $row['video_id']; ?><?php echo $previewParameter; ?>">
                                        <span class="fa fa-play"></span>
                                    </a>
                                </div>
                            </div>
                            <div class="album-info">
                                <h5><?php echo e($row['category_artist']); ?></h5>
                                <p><?php echo e($row['video_title']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No videos found.</p>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="load-more-btn text-center wow fadeInUp" data-wow-delay="300ms">
                    <a href="<?php echo e($videoMoreUrl); ?>" class="btn oneMusic-btn"><?php echo e($videoMoreText); ?> <i class="fa fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Video Area End -->

<hr>

<!-- Miscellaneous Area Start -->
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
            <div class="col-12 col-lg-4">
                <div class="weeks-top-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>Top Albums </h2>
                    </div>

                    <?php if ($topAlbums !== []): ?>
                        <?php foreach ($topAlbums as $row): ?>
                            <?php $topAlbumUrl = $homeAuthenticated ? 'album_music.php?album_id=' . (int) $row['category_id'] : 'signin.php'; ?>
                            <div class="single-top-item d-flex wow fadeInUp" data-wow-delay="350ms">
                                <div class="thumbnail">
                                    <a href="<?php echo e($topAlbumUrl); ?>">
                                        <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="<?php echo e($row['category_artist']); ?> album cover">
                                    </a>
                                </div>
                                <div class="content-">
                                    <a href="<?php echo e($topAlbumUrl); ?>">
                                        <h6><?php echo e($row['category_artist']); ?></h6>
                                        <p><?php echo e($row['category_genre']); ?></p>
                                        <span class="badge custom-badge <?php echo $homeAuthenticated ? 'bg-danger' : 'bg-success'; ?> text-white"><?php echo $homeAuthenticated ? 'New' : 'Pro'; ?></span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No top albums available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="new-hits-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>New Hits</h2>
                    </div>

                    <?php if ($newHits !== []): ?>
                        <?php foreach ($newHits as $row): ?>
                            <div class="single-new-item d-flex align-items-center justify-content-between wow fadeInUp" data-wow-delay="100ms">
                                <div class="first-part d-flex align-items-center">
                                    <div class="thumbnail">
                                        <span class="badge custom-badge bg-danger text-white">New</span>
                                        <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="Cover for <?php echo e($row['audio_title']); ?>">
                                    </div>
                                    <div class="content-">
                                        <h6><?php echo e($row['category_artist']); ?></h6>
                                        <p><?php echo e($row['audio_title']); ?></p>
                                    </div>
                                </div>
                                <audio preload="auto" controls>
                                    <source src="media.php?type=audio&amp;id=<?php echo (int) $row['audio_id']; ?><?php echo $previewParameter; ?>" type="<?php echo e($row['audio_mime_type'] ?: 'audio/mpeg'); ?>">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No new hits available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="popular-artists-area mb-100">
                    <div class="section-heading text-left mb-50 wow fadeInUp" data-wow-delay="50ms">
                        <p>See what’s new</p>
                        <h2>Popular Artist</h2>
                    </div>

                    <?php if ($popularArtists !== []): ?>
                        <?php foreach ($popularArtists as $row): ?>
                            <div class="single-artists d-flex align-items-center wow fadeInUp" data-wow-delay="50ms">
                                <div class="thumbnail">
                                    <img src="../admin/uploads/images/<?php echo e($row['category_image']); ?>" alt="<?php echo e($row['category_artist']); ?>">
                                </div>
                                <div class="content-">
                                    <a href="<?php echo $homeAuthenticated ? 'artist.php' : 'signin.php'; ?>"><?php echo e($row['category_artist']); ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No popular artists available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Miscellaneous Area End -->

<hr>

<!-- gallery -->
<div id="gallery" class="gallery_area">
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
            <?php foreach (['1.png', '12.jpg', '2.png', '3.png', '4.png', '5.png', '6.png', '7.png', '9.jpg'] as $index => $image): ?>
                <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6">
                    <div class="single-gallery mb-30">
                        <div class="portfolio-img">
                            <img src="img/gallery/<?php echo e($image); ?>" alt="One Sound gallery image <?php echo $index + 1; ?>">
                        </div>
                        <div class="gallery_hover">
                            <a class="popup-image hover_inner" href="img/gallery/<?php echo e($image); ?>">
                                <i class="ti-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!--/ gallery -->

<!-- contact_rsvp -->
<div id="contact" class="contact_rsvp">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="text text-center">
                    <h3>Send us your query anytime!</h3>
                    <a href="<?php echo e($contactUrl); ?>" class="btn oneMusic-btn"><?php echo e($contactText); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ contact_rsvp -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && typeof window.jQuery.fn.audioPlayer === 'function') {
            window.jQuery('audio').audioPlayer({});
        }
    });
</script>

<?php
if ($homeAuthenticated) {
    include dirname(__DIR__) . '/footer.php';
} else {
    include __DIR__ . '/public-footer.php';
}
?>
