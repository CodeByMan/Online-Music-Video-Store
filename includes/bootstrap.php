<?php

declare(strict_types=1);

if (!defined('ONE_SOUND_ROOT')) {
    define('ONE_SOUND_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/pagination.php';
require_once __DIR__ . '/catalog.php';

if (!date_default_timezone_set(APPLICATION_TIMEZONE)) {
    throw new RuntimeException('Invalid application timezone configuration.');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$appConfig = app_config();
$appDebug = (bool) $appConfig['debug'];

ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data:; media-src 'self'; frame-src 'self' https://maps.google.com https://www.google.com; connect-src 'self'");
    if (request_is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

start_secure_session();
$conn = database_connect($appConfig['database'], $appDebug);

set_exception_handler(static function (Throwable $exception) use ($appDebug): void {
    error_log(sprintf('[One Sound] Unhandled %s: %s in %s:%d', $exception::class, $exception->getMessage(), $exception->getFile(), $exception->getLine()));
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo $appDebug ? '<p>Application error: ' . e($exception->getMessage()) . '</p>' : '<p>Something went wrong. Please try again.</p>';
});

validate_media_storage_configuration((string) $appConfig['media_storage_path']);

