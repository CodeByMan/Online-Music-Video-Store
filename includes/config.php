<?php

declare(strict_types=1);

const ROLE_ADMIN = 1;
const ROLE_USER = 2;
const AUTH_IDLE_TIMEOUT = 7200;
const SESSION_REGENERATION_INTERVAL = 1800;
const APPLICATION_TIMEZONE = 'UTC';
const DATABASE_TIMEZONE_OFFSET = '+00:00';
const MAX_IMAGE_WIDTH = 5000;
const MAX_IMAGE_HEIGHT = 5000;
const MAX_ORIGINAL_FILE_NAME_LENGTH = 180;

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function env_bool(string $key, bool $default = false): bool
{
    $value = env_value($key);
    if ($value === null) {
        return $default;
    }

    return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
}

function app_config(): array
{
    $config = [
        'environment' => env_value('APP_ENV', 'production'),
        'debug' => env_bool('APP_DEBUG', false),
        'database' => [
            'host' => env_value('DB_HOST', '127.0.0.1'),
            'port' => (int) env_value('DB_PORT', '3306'),
            'name' => env_value('DB_DATABASE', 'sounds'),
            'user' => env_value('DB_USERNAME', 'root'),
            'password' => env_value('DB_PASSWORD', ''),
        ],
        'media_storage_path' => env_value('MEDIA_STORAGE_PATH', default_media_storage_path()),
    ];

    $localConfigPath = ONE_SOUND_ROOT . '/config.local.php';
    if (is_file($localConfigPath)) {
        $local = require $localConfigPath;
        if (is_array($local)) {
            // Backward compatibility with the previous flat database-only local config.
            if (isset($local['host'], $local['name'], $local['user'])) {
                $config['database'] = array_replace($config['database'], $local);
            } else {
                $config = array_replace_recursive($config, $local);
            }
        }
    }

    return $config;
}

function default_media_storage_path(): string
{
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (is_string($documentRoot) && $documentRoot !== '') {
        return dirname(rtrim($documentRoot, '/\\')) . DIRECTORY_SEPARATOR . 'one-sound-private-media';
    }

    return dirname(dirname(ONE_SOUND_ROOT)) . DIRECTORY_SEPARATOR . 'one-sound-private-media';
}

function validate_media_storage_configuration(string $path): void
{
    if ($path === '' || !preg_match('~^(?:[A-Za-z]:[\\\\/]|/)~', $path)) {
        throw new RuntimeException('MEDIA_STORAGE_PATH must be an absolute path outside the public document root.');
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (!is_string($documentRoot) || $documentRoot === '') {
        return;
    }

    $normalize = static function (string $value): string {
        $resolved = realpath($value);
        $normalized = str_replace('\\', '/', rtrim($resolved !== false ? $resolved : $value, '/\\'));
        return DIRECTORY_SEPARATOR === '\\' ? strtolower($normalized) : $normalized;
    };
    $mediaPath = $normalize($path);
    $publicRoot = $normalize($documentRoot);
    if ($mediaPath === $publicRoot || str_starts_with($mediaPath . '/', $publicRoot . '/')) {
        throw new RuntimeException('MEDIA_STORAGE_PATH must not be inside the public document root.');
    }
}
