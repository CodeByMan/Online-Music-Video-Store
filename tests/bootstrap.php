<?php

declare(strict_types=1);

define('ONE_SOUND_ROOT', dirname(__DIR__));
require_once ONE_SOUND_ROOT . '/includes/config.php';
require_once ONE_SOUND_ROOT . '/includes/session.php';
require_once ONE_SOUND_ROOT . '/includes/validation.php';
require_once ONE_SOUND_ROOT . '/includes/database.php';
require_once ONE_SOUND_ROOT . '/includes/auth.php';
require_once ONE_SOUND_ROOT . '/includes/upload.php';

date_default_timezone_set(APPLICATION_TIMEZONE);
