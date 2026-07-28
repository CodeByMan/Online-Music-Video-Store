<?php

// Copy this file to config.local.php. Never commit config.local.php.
// APP_DEBUG defaults to false; enable it only on a trusted local machine.
return [
    'environment' => 'local',
    'debug' => false,
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'sounds',
        'user' => 'root',
        'password' => '',
    ],
    // Use a directory outside Apache/Nginx's public document root.
    // Example for XAMPP: C:\\xampp\\one-sound-private-media
    'media_storage_path' => 'C:\\xampp\\one-sound-private-media',
];
