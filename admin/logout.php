<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_post_request();
try {
    verify_csrf();
    clear_authenticated_session();
} catch (Throwable $exception) {
    error_log('[One Sound] Admin logout failed: ' . $exception->getMessage());
}
redirect('login.php');
