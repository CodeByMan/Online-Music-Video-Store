<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

function schema_table_exists(mysqli $connection, string $table): bool
{
    return db_fetch_one(
        $connection,
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
        's',
        [$table]
    ) !== null;
}

function schema_column_exists(mysqli $connection, string $table, string $column): bool
{
    return db_fetch_one(
        $connection,
        'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
        'ss',
        [$table, $column]
    ) !== null;
}

function schema_index_exists(mysqli $connection, string $table, string $index): bool
{
    return db_fetch_one(
        $connection,
        'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
        'ss',
        [$table, $index]
    ) !== null;
}

function schema_constraint_exists(mysqli $connection, string $table, string $constraint): bool
{
    return db_fetch_one(
        $connection,
        'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
        'ss',
        [$table, $constraint]
    ) !== null;
}


function schema_foreign_key_exists(mysqli $connection, string $table, string $column, string $referencedTable): bool
{
    return db_fetch_one(
        $connection,
        'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ? LIMIT 1',
        'sss',
        [$table, $column, $referencedTable]
    ) !== null;
}

function schema_add_foreign_key(mysqli $connection, string $table, string $column, string $referencedTable, string $referencedColumn, string $constraint, string $onDelete): void
{
    if (!schema_foreign_key_exists($connection, $table, $column, $referencedTable)) {
        db_execute($connection, sprintf(
            'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON UPDATE CASCADE ON DELETE %s',
            $table,
            $constraint,
            $column,
            $referencedTable,
            $referencedColumn,
            $onDelete
        ));
        echo "Added foreign key {$table}.{$column}\n";
    }
}

function migration_truncate(string $value, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, $length, 'UTF-8') : substr($value, 0, $length);
}

function schema_add_column(mysqli $connection, string $table, string $column, string $definition): void
{
    if (!schema_column_exists($connection, $table, $column)) {
        db_execute($connection, sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
        echo "Added {$table}.{$column}\n";
    }
}

function schema_add_index(mysqli $connection, string $table, string $index, string $columns, bool $unique = false): void
{
    if (!schema_index_exists($connection, $table, $index)) {
        db_execute($connection, sprintf('ALTER TABLE `%s` ADD %s INDEX `%s` (%s)', $table, $unique ? 'UNIQUE' : '', $index, $columns));
        echo "Added index {$table}.{$index}\n";
    }
}

function schema_add_constraint(mysqli $connection, string $table, string $constraint, string $definition): void
{
    if (!schema_constraint_exists($connection, $table, $constraint)) {
        db_execute($connection, sprintf('ALTER TABLE `%s` ADD CONSTRAINT `%s` %s', $table, $constraint, $definition));
        echo "Added constraint {$table}.{$constraint}\n";
    }
}

function duplicate_count(mysqli $connection, string $table, string $groupColumns): int
{
    $row = db_fetch_one($connection, "SELECT COUNT(*) AS total FROM (SELECT 1 FROM `{$table}` GROUP BY {$groupColumns} HAVING COUNT(*) > 1) duplicates");
    return (int) ($row['total'] ?? 0);
}

function migrate_legacy_media(mysqli $connection, string $table, string $idColumn, string $nameColumn, string $titleColumn, string $originalColumn, string $mimeColumn, string $sizeColumn, string $type, string $legacyDirectory): array
{
    $summary = ['migrated' => 0, 'already_private' => 0, 'missing' => 0, 'invalid' => 0];
    $targetDirectory = media_storage_directory($type);
    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('Unable to create private media directory: ' . $targetDirectory);
    }

    $rows = db_fetch_all($connection, "SELECT `{$idColumn}` AS media_id, `{$nameColumn}` AS stored_name, `{$titleColumn}` AS media_title, `{$originalColumn}` AS original_name FROM `{$table}` ORDER BY `{$idColumn}`");
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($rows as $row) {
        $id = (int) $row['media_id'];
        $oldName = basename((string) $row['stored_name']);
        $privatePath = $targetDirectory . DIRECTORY_SEPARATOR . $oldName;
        if ($oldName !== '' && is_file($privatePath)) {
            $summary['already_private']++;
            continue;
        }

        $legacyPath = rtrim($legacyDirectory, '/\\') . DIRECTORY_SEPARATOR . $oldName;
        if ($oldName === '' || !is_file($legacyPath)) {
            $summary['missing']++;
            echo "Missing legacy {$type} file for {$table} ID {$id}: {$oldName}\n";
            continue;
        }

        try {
            validate_media_signature($legacyPath, $type);
        } catch (Throwable $exception) {
            $summary['invalid']++;
            echo "Rejected invalid legacy {$type} file for {$table} ID {$id}: {$oldName} ({$exception->getMessage()})\n";
            continue;
        }

        $extension = $type === 'audio' ? 'mp3' : 'mp4';
        $newName = bin2hex(random_bytes(16)) . '.' . $extension;
        $newPath = $targetDirectory . DIRECTORY_SEPARATOR . $newName;
        if (!copy($legacyPath, $newPath)) {
            throw new RuntimeException('Unable to copy legacy media file: ' . $legacyPath);
        }
        @chmod($newPath, 0640);

        $originalName = (string) ($row['original_name'] ?: $oldName);
        $title = (string) ($row['media_title'] ?: pathinfo($originalName, PATHINFO_FILENAME));
        $mime = (string) ($finfo->file($newPath) ?: ($type === 'audio' ? 'audio/mpeg' : 'video/mp4'));
        $size = (int) filesize($newPath);

        try {
            db_execute(
                $connection,
                "UPDATE `{$table}` SET `{$titleColumn}` = ?, `{$nameColumn}` = ?, `{$originalColumn}` = ?, `{$mimeColumn}` = ?, `{$sizeColumn}` = ? WHERE `{$idColumn}` = ?",
                'ssssii',
                [migration_truncate($title, 180), $newName, migration_truncate($originalName, 180), $mime, $size, $id]
            );
        } catch (Throwable $exception) {
            @unlink($newPath);
            throw $exception;
        }
        $summary['migrated']++;
    }

    return $summary;
}

$requiredTables = ['roles', 'users', 'categories', 'audio', 'videos', 'messages', 'review_audio'];
foreach ($requiredTables as $table) {
    if (!schema_table_exists($conn, $table)) {
        fwrite(STDERR, "Missing required table '{$table}'. Import database/sounds.sql for a fresh installation.\n");
        exit(1);
    }
}

try {
    echo "Starting One Sound database migration...\n";
    schema_add_column($conn, 'roles', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'roles', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    schema_add_column($conn, 'users', 'session_version', 'INT UNSIGNED NOT NULL DEFAULT 1');
    schema_add_column($conn, 'users', 'account_status', "ENUM('active','disabled') NOT NULL DEFAULT 'active'");
    schema_add_column($conn, 'users', 'disabled_at', 'DATETIME NULL');
    schema_add_column($conn, 'users', 'last_login_at', 'DATETIME NULL');
    schema_add_column($conn, 'users', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'users', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    schema_add_column($conn, 'categories', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'categories', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    schema_add_column($conn, 'audio', 'audio_title', "VARCHAR(180) NOT NULL DEFAULT '' AFTER audio_id");
    schema_add_column($conn, 'audio', 'audio_original_name', "VARCHAR(180) NOT NULL DEFAULT '' AFTER audio_name");
    schema_add_column($conn, 'audio', 'audio_mime_type', "VARCHAR(100) NOT NULL DEFAULT 'audio/mpeg' AFTER audio_original_name");
    schema_add_column($conn, 'audio', 'audio_size_bytes', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER audio_mime_type');
    schema_add_column($conn, 'audio', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'audio', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    db_execute($conn, "UPDATE audio SET audio_title = LEFT(CASE WHEN LOCATE('.', REVERSE(audio_name)) > 0 THEN LEFT(audio_name, LENGTH(audio_name) - LOCATE('.', REVERSE(audio_name))) ELSE audio_name END, 180) WHERE audio_title = ''");
    db_execute($conn, "UPDATE audio SET audio_original_name = LEFT(audio_name, 180) WHERE audio_original_name = ''");

    schema_add_column($conn, 'videos', 'video_title', "VARCHAR(180) NOT NULL DEFAULT '' AFTER video_id");
    schema_add_column($conn, 'videos', 'video_original_name', "VARCHAR(180) NOT NULL DEFAULT '' AFTER video_name");
    schema_add_column($conn, 'videos', 'video_mime_type', "VARCHAR(100) NOT NULL DEFAULT 'video/mp4' AFTER video_original_name");
    schema_add_column($conn, 'videos', 'video_size_bytes', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER video_mime_type');
    schema_add_column($conn, 'videos', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'videos', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    db_execute($conn, "UPDATE videos SET video_title = LEFT(CASE WHEN LOCATE('.', REVERSE(video_name)) > 0 THEN LEFT(video_name, LENGTH(video_name) - LOCATE('.', REVERSE(video_name))) ELSE video_name END, 180) WHERE video_title = ''");
    db_execute($conn, "UPDATE videos SET video_original_name = LEFT(video_name, 180) WHERE video_original_name = ''");

    schema_add_column($conn, 'messages', 'read_at', 'DATETIME NULL');
    schema_add_column($conn, 'messages', 'read_by', 'INT NULL');
    schema_add_column($conn, 'messages', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    schema_add_column($conn, 'messages', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    schema_add_column($conn, 'review_audio', 'updated_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    if (!schema_table_exists($conn, 'request_limits')) {
        db_execute($conn, "CREATE TABLE request_limits (
            limit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            action_name VARCHAR(40) NOT NULL,
            rate_key CHAR(64) NOT NULL,
            attempt_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            window_started_at DATETIME NOT NULL,
            last_attempt_at DATETIME NOT NULL,
            blocked_until DATETIME NULL,
            PRIMARY KEY (limit_id),
            UNIQUE KEY uq_request_limit_action_key (action_name, rate_key),
            KEY idx_request_limits_cleanup (blocked_until, last_attempt_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "Created request_limits table\n";
    }

    db_execute($conn, "INSERT INTO roles (role_id, role_type) VALUES (1, 'Administrator') ON DUPLICATE KEY UPDATE role_type = VALUES(role_type)");
    db_execute($conn, "INSERT INTO roles (role_id, role_type) VALUES (2, 'User') ON DUPLICATE KEY UPDATE role_type = VALUES(role_type)");
    db_execute($conn, 'UPDATE users SET role_id = ? WHERE role_id NOT IN (?, ?)', 'iii', [ROLE_USER, ROLE_ADMIN, ROLE_USER]);
    db_execute($conn, 'DELETE FROM roles WHERE role_id NOT IN (?, ?)', 'ii', [ROLE_ADMIN, ROLE_USER]);

    if (duplicate_count($conn, 'users', 'LOWER(user_email)') > 0) {
        throw new RuntimeException('Duplicate user emails must be resolved before adding the unique email constraint.');
    }
    schema_add_index($conn, 'users', 'uq_users_email', '`user_email`', true);
    schema_add_index($conn, 'users', 'idx_users_status', '`account_status`');
    schema_add_index($conn, 'categories', 'idx_categories_artist', '`category_artist`');
    schema_add_index($conn, 'categories', 'idx_categories_album', '`category_album`');
    schema_add_index($conn, 'categories', 'idx_categories_genre', '`category_genre`');
    schema_add_index($conn, 'categories', 'idx_categories_language', '`category_language`');
    schema_add_index($conn, 'categories', 'idx_categories_year', '`category_year`');
    schema_add_index($conn, 'audio', 'idx_audio_title', '`audio_title`');
    schema_add_index($conn, 'videos', 'idx_video_title', '`video_title`');
    schema_add_index($conn, 'messages', 'idx_messages_status_created', '`message_status`, `created_at`');

    db_execute($conn, 'DELETE older FROM review_audio older INNER JOIN review_audio newer ON older.audio_id = newer.audio_id AND older.user_id = newer.user_id AND older.review_id < newer.review_id');
    schema_add_index($conn, 'review_audio', 'uq_review_audio_user', '`audio_id`, `user_id`', true);
    schema_add_index($conn, 'review_audio', 'idx_review_date', '`review_date`');

    schema_add_constraint($conn, 'categories', 'chk_category_year', 'CHECK (`category_year` BETWEEN 1900 AND 2100)');
    schema_add_constraint($conn, 'review_audio', 'chk_review_rating', 'CHECK (`review_rating` BETWEEN 1 AND 5)');

    schema_add_foreign_key($conn, 'users', 'role_id', 'roles', 'role_id', 'fk_users_role', 'RESTRICT');
    schema_add_foreign_key($conn, 'audio', 'category_id', 'categories', 'category_id', 'fk_audio_category', 'CASCADE');
    schema_add_foreign_key($conn, 'videos', 'category_id', 'categories', 'category_id', 'fk_video_category', 'CASCADE');
    schema_add_foreign_key($conn, 'review_audio', 'audio_id', 'audio', 'audio_id', 'fk_review_audio', 'CASCADE');
    schema_add_foreign_key($conn, 'review_audio', 'user_id', 'users', 'user_id', 'fk_review_user', 'CASCADE');
    schema_add_foreign_key($conn, 'messages', 'read_by', 'users', 'user_id', 'fk_messages_read_by', 'SET NULL');

    $audioSummary = migrate_legacy_media($conn, 'audio', 'audio_id', 'audio_name', 'audio_title', 'audio_original_name', 'audio_mime_type', 'audio_size_bytes', 'audio', dirname(__DIR__) . '/admin/uploads/music');
    $videoSummary = migrate_legacy_media($conn, 'videos', 'video_id', 'video_name', 'video_title', 'video_original_name', 'video_mime_type', 'video_size_bytes', 'video', dirname(__DIR__) . '/admin/uploads/videos');

    db_execute($conn, 'DELETE FROM request_limits WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL 7 DAY)');

    echo "Migration complete.\n";
    echo sprintf("Audio: %d migrated, %d already private, %d missing, %d invalid.\n", $audioSummary['migrated'], $audioSummary['already_private'], $audioSummary['missing'], $audioSummary['invalid']);
    echo sprintf("Video: %d migrated, %d already private, %d missing, %d invalid.\n", $videoSummary['migrated'], $videoSummary['already_private'], $videoSummary['missing'], $videoSummary['invalid']);
    echo "After verifying playback, remove legacy public audio/video files.\n";
} catch (Throwable $exception) {
    // ALTER TABLE statements auto-commit in MySQL/MariaDB. Re-run this idempotent
    // script after correcting the reported problem rather than assuming rollback.
    fwrite(STDERR, 'Migration stopped: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
