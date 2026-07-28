<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_post_request();
try {
    verify_csrf();
    clear_authenticated_session();
} catch (Throwable $exception) {
    error_log('[One Sound] Frontend logout failed: ' . $exception->getMessage());
}
redirect('signin.php');
