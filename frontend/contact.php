<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
$current_user = require_frontend_user($conn);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        verify_csrf();
        $messageName = input_text($_POST, 'name', 2, 100);
        $messageEmail = input_email($_POST);
        enforce_action_rate_limit($conn, 'contact_message', $messageEmail, 5, 600, 900);
        $messageSubject = input_text($_POST, 'subject', 2, 150);
        $messageDetail = input_text($_POST, 'detail', 2, 2000, true);

        db_execute(
            $conn,
            'INSERT INTO messages (message_name, message_email, message_subject, message_detail) VALUES (?, ?, ?, ?)',
            'ssss',
            [$messageName, $messageEmail, $messageSubject, $messageDetail]
        );
        redirect_with_flash('contact.php', 'Message sent successfully.');
    } catch (Throwable $exception) {
        error_log('[One Sound] Contact message error: ' . $exception->getMessage());
        $message = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Unable to send the message. Please try again.';
        redirect_with_flash('contact.php', $message, 'danger');
    }
}
include 'header.php';
?>
<div class="bradcam_area breadcam_bg_2"><div class="container"><div class="row"><div class="col-xl-12"><div class="bradcam_text text-center"><h3>contact</h3></div></div></div></div></div>
<section class="contact-section section_padding"><div class="container"><div class="row">
<div class="col-12"><h2 class="contact-title">Get in Touch</h2></div>
<div class="col-lg-8 col-md-12">
<form action="contact.php" method="post">
<?php echo csrf_field(); ?>
<div class="row">
<div class="col-sm-12 col-md-6"><div class="form-group"><label class="sr-only" for="name">Name</label><input class="form-control" name="name" id="name" type="text" placeholder="Name" maxlength="100" required style="border: 2px solid #555; border-radius: 5px;"></div></div>
<div class="col-sm-12 col-md-6"><div class="form-group"><label class="sr-only" for="email">Email</label><input type="email" name="email" class="form-control" id="email" placeholder="E-mail" maxlength="254" required style="border: 2px solid #555; border-radius: 5px;"></div></div>
<div class="col-12"><div class="form-group"><label class="sr-only" for="subject">Subject</label><input type="text" name="subject" class="form-control" id="subject" placeholder="Subject" maxlength="150" required style="border: 2px solid #555; border-radius: 5px;"></div></div>
<div class="col-12"><div class="form-group"><label class="sr-only" for="message">Message</label><textarea name="detail" class="form-control w-100" id="message" cols="30" rows="9" placeholder="Message" maxlength="2000" required style="border: 2px solid #555; border-radius: 5px;"></textarea></div></div>
</div>
<div class="form-group mt-3 mb-5"><button type="submit" class="btn oneMusic-btn">Send Message</button></div>
</form>
</div>
<div class="col-lg-4 col-md-12">
<div class="media contact-info"><span class="contact-info__icon"><i class="ti-home"></i></span><div class="media-body"><h3>Demo Office</h3><p>Karachi, Pakistan</p></div></div>
<div class="media contact-info"><span class="contact-info__icon"><i class="ti-tablet"></i></span><div class="media-body"><h3>+92 300 0000000</h3><p>Fictional portfolio contact</p></div></div>
<div class="media contact-info"><span class="contact-info__icon"><i class="ti-email"></i></span><div class="media-body"><h3>support@example.test</h3><p>Fictional demonstration address</p></div></div>
</div>
</div>
<div class="mapouter"><div class="gmap_canvas"><iframe title="Map showing Karachi" class="gmap_iframe" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=657&amp;height=477&amp;hl=en&amp;q=Karachi%20Pakistan&amp;t=&amp;z=11&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe></div><style>.mapouter{position:relative;text-align:right;width:100%;height:477px;}.gmap_canvas{overflow:hidden;background:none!important;width:100%;height:477px;}.gmap_iframe{height:477px!important;}</style></div>
</div></section>
<?php include 'footer.php'; ?>
