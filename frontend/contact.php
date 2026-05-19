<?php 
session_start();
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $message_name = $_POST['name'];
    $message_email = $_POST['email'];
    $message_subject = $_POST['subject'];
    $message_detail = $_POST['detail'];

    // Insert into the database
    $sql = "INSERT INTO `messages`(`message_name`, `message_email`, `message_subject`, `message_detail`)
            VALUES ('$message_name', '$message_email', '$message_subject', '$message_detail')";

    // Check if query was successful
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Message Sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

    <!-- bradcam_area  -->
    <div class="bradcam_area breadcam_bg_2">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="bradcam_text text-center">
                        <h3>contact</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ bradcam_area  -->

<!-- ================ contact section start ================= -->
<section class="contact-section section_padding">
    <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="contact-title">Get in Touch</h2>
                </div>

                <!-- Form Section -->
                <div class="col-lg-8 col-md-12">
                    <form action="#" method="post">

                        <div class="row">
                            <!-- Name Input -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <input class="form-control" name="name" id="name" type="text" placeholder="Name" required 
                                           style="border: 2px solid #555; border-radius: 5px;">
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div class="col-sm-12 col-md-6">
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="E-mail" required
                                           style="border: 2px solid #555; border-radius: 5px;">
                                </div>
                            </div>

                            <!-- Subject Input -->
                            <div class="col-12">
                                <div class="form-group">
                                    <input type="text" name="subject" class="form-control" id="subject" placeholder="Subject" required
                                           style="border: 2px solid #555; border-radius: 5px;">
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="col-12">
                                <div class="form-group">
                                    <textarea name="detail" class="form-control w-100" id="message" cols="30" rows="9" placeholder="Message" required
                                              style="border: 2px solid #555; border-radius: 5px;"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3 mb-5">
                            <button type="submit" class="btn oneMusic-btn">
                                Send Message
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Contact Info Section -->
                <div class="col-lg-4 col-md-12">
                    <div class="media contact-info">
                        <span class="contact-info__icon"><i class="ti-home"></i></span>
                        <div class="media-body">
                            <h3>Aptech, North Nazimabad</h3>
                            <p>Karachi, Pakistan</p>
                        </div>
                    </div>
                    <div class="media contact-info">
                        <span class="contact-info__icon"><i class="ti-tablet"></i></span>
                        <div class="media-body">
                            <h3>00 (123) 4567 890</h3>
                            <p>Mon to Sat 9am to 9pm</p>
                        </div>
                    </div>
                    <div class="media contact-info">
                        <span class="contact-info__icon"><i class="ti-email"></i></span>
                        <div class="media-body">
                            <h3>support@CodeByMan.com</h3>
                            <p>Send us your query anytime!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Map Section -->
            <div class="mapouter">
                <div class="gmap_canvas">
                    <iframe class="gmap_iframe" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=657&amp;height=477&amp;hl=en&amp;q=karachi aptech north nazimbad&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
                        <a href="https://sprunkin.com/">Sprunki Phases</a>
                </div>
                <style>.mapouter{position:relative;text-align:right;width:100%;height:477px;}.gmap_canvas {overflow:hidden;background:none!important;width:100%;height:477px;}.gmap_iframe {height:477px!important;}</style>
            </div>
        
    </div>
</section>
<!-- ================ contact section end ================= -->

<?php
include 'footer.php';
?>
