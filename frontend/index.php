<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

// Authenticated replica of the original One Sound index.php frontend.
$current_user = require_frontend_user($conn);
$homeAuthenticated = true;
include 'partials/home-page.php';
