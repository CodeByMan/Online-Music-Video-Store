<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/includes/bootstrap.php';

/**
 * @return array<string, string>
 */
function cli_options(): array
{
    $options = getopt('', ['audio-source:', 'video-source:']);
    if (!is_array($options)) {
        return [];
    }

    return array_map(static fn (mixed $value): string => (string) $value, $options);
}

/**
 * @param list<string> $candidates
 */
function locate_source_file(string $directory, array $candidates): ?string
{
    if (!is_dir($directory)) {
        return null;
    }

    foreach ($candidates as $candidate) {
        $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . basename($candidate);
        if (is_file($path) && is_readable($path)) {
            return $path;
        }
    }

    $files = scandir($directory);
    if ($files === false) {
        return null;
    }

    $map = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $file;
        if (is_file($path) && is_readable($path)) {
            $map[strtolower($file)] = $path;
        }
    }

    foreach ($candidates as $candidate) {
        $match = $map[strtolower(basename($candidate))] ?? null;
        if (is_string($match)) {
            return $match;
        }
    }

    return null;
}

function normalized_media_mime(string $path, string $type): string
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);

    if ($type === 'audio') {
        return is_string($mime) && str_starts_with($mime, 'audio/') ? $mime : 'audio/mpeg';
    }

    return is_string($mime) && in_array($mime, ['video/mp4', 'application/mp4'], true)
        ? 'video/mp4'
        : 'video/mp4';
}

/**
 * @return array{restored:int,existing:int,missing:int,invalid:int}
 */
function restore_media_type(mysqli $connection, string $type, string $sourceDirectory): array
{
    $isAudio = $type === 'audio';
    $table = $isAudio ? 'audio' : 'videos';
    $idColumn = $isAudio ? 'audio_id' : 'video_id';
    $nameColumn = $isAudio ? 'audio_name' : 'video_name';
    $titleColumn = $isAudio ? 'audio_title' : 'video_title';
    $originalColumn = $isAudio ? 'audio_original_name' : 'video_original_name';
    $mimeColumn = $isAudio ? 'audio_mime_type' : 'video_mime_type';
    $sizeColumn = $isAudio ? 'audio_size_bytes' : 'video_size_bytes';
    $extension = $isAudio ? 'mp3' : 'mp4';
    $targetDirectory = media_storage_directory($type);

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('Unable to create private media directory: ' . $targetDirectory);
    }

    $summary = ['restored' => 0, 'existing' => 0, 'missing' => 0, 'invalid' => 0];
    $rows = db_fetch_all(
        $connection,
        "SELECT {$idColumn} AS media_id,
                {$nameColumn} AS stored_name,
                {$titleColumn} AS media_title,
                {$originalColumn} AS original_name
         FROM {$table}
         ORDER BY {$idColumn}"
    );

    foreach ($rows as $row) {
        $id = (int) $row['media_id'];
        $storedName = basename(trim((string) $row['stored_name']));
        $existingPath = $storedName === ''
            ? ''
            : $targetDirectory . DIRECTORY_SEPARATOR . $storedName;

        if ($existingPath !== '' && is_file($existingPath) && is_readable($existingPath)) {
            $summary['existing']++;
            echo sprintf("%s ID %d already exists in private storage.\n", ucfirst($type), $id);
            continue;
        }

        $originalName = basename(trim((string) $row['original_name']));
        $title = trim((string) $row['media_title']);
        $candidates = array_values(array_unique(array_filter([
            $originalName,
            $storedName,
            $title !== '' ? $title . '.' . $extension : '',
        ], static fn (string $value): bool => $value !== '')));

        $sourcePath = locate_source_file($sourceDirectory, $candidates);
        if ($sourcePath === null) {
            $summary['missing']++;
            echo sprintf(
                "Missing source file for %s ID %d. Tried: %s\n",
                $type,
                $id,
                $candidates === [] ? '(no filename metadata)' : implode(', ', $candidates)
            );
            continue;
        }

        try {
            validate_media_signature($sourcePath, $type);
        } catch (Throwable $exception) {
            $summary['invalid']++;
            echo sprintf(
                "Rejected %s for %s ID %d: %s\n",
                basename($sourcePath),
                $type,
                $id,
                $exception->getMessage()
            );
            continue;
        }

        $newStoredName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $newStoredName;

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Unable to copy ' . $sourcePath . ' to private storage.');
        }
        @chmod($targetPath, 0640);

        $resolvedOriginalName = $originalName !== '' ? $originalName : basename($sourcePath);
        $resolvedTitle = $title !== '' ? $title : pathinfo($resolvedOriginalName, PATHINFO_FILENAME);
        $mime = normalized_media_mime($targetPath, $type);
        $size = filesize($targetPath);

        if ($size === false || $size < 1) {
            @unlink($targetPath);
            throw new RuntimeException('Copied media file has an invalid size: ' . $targetPath);
        }

        try {
            db_execute(
                $connection,
                "UPDATE {$table}
                 SET {$nameColumn} = ?,
                     {$titleColumn} = ?,
                     {$originalColumn} = ?,
                     {$mimeColumn} = ?,
                     {$sizeColumn} = ?
                 WHERE {$idColumn} = ?",
                'ssssii',
                [
                    $newStoredName,
                    mb_substr($resolvedTitle, 0, 180, 'UTF-8'),
                    mb_substr($resolvedOriginalName, 0, 180, 'UTF-8'),
                    $mime,
                    (int) $size,
                    $id,
                ]
            );
        } catch (Throwable $exception) {
            @unlink($targetPath);
            throw $exception;
        }

        $summary['restored']++;
        echo sprintf(
            "Restored %s ID %d from %s.\n",
            $type,
            $id,
            basename($sourcePath)
        );
    }

    return $summary;
}

$options = cli_options();
$audioSource = $options['audio-source'] ?? '';
$videoSource = $options['video-source'] ?? '';

if ($audioSource === '' || $videoSource === '') {
    fwrite(
        STDERR,
        "Usage: php scripts/restore_private_media.php --audio-source=PATH --video-source=PATH\n"
    );
    exit(1);
}

if (!is_dir($audioSource) || !is_dir($videoSource)) {
    fwrite(STDERR, "The supplied audio or video source directory does not exist.\n");
    exit(1);
}

try {
    echo "Restoring One Sound media into private storage...\n";
    echo 'Audio source: ' . $audioSource . PHP_EOL;
    echo 'Video source: ' . $videoSource . PHP_EOL;
    echo 'Private root: ' . (string) $appConfig['media_storage_path'] . PHP_EOL . PHP_EOL;

    $audioSummary = restore_media_type($conn, 'audio', $audioSource);
    $videoSummary = restore_media_type($conn, 'video', $videoSource);

    echo PHP_EOL;
    echo sprintf(
        "Audio: %d restored, %d already present, %d missing, %d invalid.\n",
        $audioSummary['restored'],
        $audioSummary['existing'],
        $audioSummary['missing'],
        $audioSummary['invalid']
    );
    echo sprintf(
        "Video: %d restored, %d already present, %d missing, %d invalid.\n",
        $videoSummary['restored'],
        $videoSummary['existing'],
        $videoSummary['missing'],
        $videoSummary['invalid']
    );

    $problems = $audioSummary['missing'] + $audioSummary['invalid']
        + $videoSummary['missing'] + $videoSummary['invalid'];

    if ($problems > 0) {
        fwrite(STDERR, "Media restoration completed with unresolved files.\n");
        exit(1);
    }

    echo "Media restoration completed successfully.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Media restoration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
