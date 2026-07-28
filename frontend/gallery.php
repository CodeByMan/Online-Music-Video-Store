<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);
$galleryImages = ['1.png', '12.jpg', '2.png', '3.png', '4.png', '5.png', '6.png', '8.png', '10.png', '16.jpg', '11.jpg', '9.jpg', '13.jpg', '14.jpg', '15.jpg', '7.png', '17.jpg', '18.jpg'];
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>Gallery</h3></div></div></div></div></div>
<div class="gallery_area"><div class="container"><div class="row grid">
<?php foreach ($galleryImages as $index => $image): ?>
    <div class="col-xl-4 col-lg-6 grid-item cat2 col-md-6"><div class="single-gallery mb-30"><div class="portfolio-img"><img src="img/gallery/<?php echo e($image); ?>" alt="One Sound gallery image <?php echo $index + 1; ?>"></div><div class="gallery_hover"><a class="popup-image hover_inner" href="img/gallery/<?php echo e($image); ?>" aria-label="Open gallery image <?php echo $index + 1; ?>"><i class="ti-plus" aria-hidden="true"></i></a></div></div></div>
<?php endforeach; ?>
</div></div></div>
<?php include 'footer.php'; ?>
