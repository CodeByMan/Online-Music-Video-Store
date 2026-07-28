<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

/**
 * The public homepage may stream only the media records displayed as previews.
 * Full catalog playback remains limited to authenticated users.
 */
function is_public_home_preview(mysqli $connection, string $type, int $id): bool
{
    $table = $type === 'audio' ? 'audio' : 'videos';
    $idColumn = $type === 'audio' ? 'audio_id' : 'video_id';
    $limit = $type === 'audio' ? 4 : 6;

    $rows = db_fetch_all(
        $connection,
        "SELECT {$idColumn} AS media_id FROM {$table} ORDER BY {$idColumn} DESC LIMIT {$limit}"
    );

    foreach ($rows as $row) {
        if ((int) $row['media_id'] === $id) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<string, mixed> $media
 * @return list<string>
 */
function media_candidate_names(array $media, string $type): array
{
    $extension = $type === 'audio' ? 'mp3' : 'mp4';
    $candidates = [];

    foreach (['stored_name', 'original_name'] as $key) {
        $name = basename(trim((string) ($media[$key] ?? '')));
        if ($name !== '') {
            $candidates[] = $name;
        }
    }

    $title = trim((string) ($media['media_title'] ?? ''));
    if ($title !== '') {
        $candidates[] = basename($title) . '.' . $extension;
    }

    return array_values(array_unique($candidates));
}

/**
 * Find a media file by exact name first, then by case-insensitive filename.
 *
 * @param list<string> $candidateNames
 */
function find_media_file(string $directory, array $candidateNames): ?string
{
    if (!is_dir($directory)) {
        return null;
    }

    foreach ($candidateNames as $candidateName) {
        $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . basename($candidateName);
        if (is_file($path) && is_readable($path)) {
            return $path;
        }
    }

    $availableFiles = scandir($directory);
    if ($availableFiles === false) {
        return null;
    }

    $caseInsensitiveMap = [];
    foreach ($availableFiles as $availableFile) {
        if ($availableFile === '.' || $availableFile === '..') {
            continue;
        }

        $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $availableFile;
        if (is_file($path) && is_readable($path)) {
            $caseInsensitiveMap[strtolower($availableFile)] = $path;
        }
    }

    foreach ($candidateNames as $candidateName) {
        $match = $caseInsensitiveMap[strtolower(basename($candidateName))] ?? null;
        if (is_string($match)) {
            return $match;
        }
    }

    return null;
}

function media_response_mime(string $path, string $type, string $databaseMime): string
{
    $fallback = $type === 'audio' ? 'audio/mpeg' : 'video/mp4';
    $allowed = $type === 'audio'
        ? ['audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg', 'audio/x-mp3', 'audio/x-mpeg-3']
        : ['video/mp4', 'application/mp4'];

    $detected = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (is_string($detected) && in_array($detected, $allowed, true)) {
        return $detected === 'application/mp4' ? 'video/mp4' : $detected;
    }

    if (in_array($databaseMime, $allowed, true)) {
        return $databaseMime === 'application/mp4' ? 'video/mp4' : $databaseMime;
    }

    return $fallback;
}

/**
 * @return array{start:int,end:int,status:int}
 */
function requested_media_range(int $size): array
{
    $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
    if (!is_string($rangeHeader) || trim($rangeHeader) === '') {
        return ['start' => 0, 'end' => $size - 1, 'status' => 200];
    }

    if (str_contains($rangeHeader, ',')) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    if (preg_match('/^bytes=(\d*)-(\d*)$/i', trim($rangeHeader), $matches) !== 1) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    $startText = $matches[1];
    $endText = $matches[2];

    if ($startText === '' && $endText === '') {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    if ($startText === '') {
        $suffixLength = (int) $endText;
        if ($suffixLength < 1) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            exit;
        }

        $suffixLength = min($suffixLength, $size);
        return [
            'start' => $size - $suffixLength,
            'end' => $size - 1,
            'status' => 206,
        ];
    }

    $start = (int) $startText;
    $end = $endText === '' ? $size - 1 : min((int) $endText, $size - 1);

    if ($start < 0 || $start >= $size || $end < $start) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }

    return ['start' => $start, 'end' => $end, 'status' => 206];
}

try {
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        header('Allow: GET, HEAD');
        http_response_code(405);
        exit('Method not allowed.');
    }

    $type = input_text($_GET, 'type', 5, 5);
    if (!in_array($type, ['audio', 'video'], true)) {
        throw new InvalidArgumentException('Invalid media type.');
    }

    $id = request_positive_int($_GET, 'id');
    $authenticatedUser = authenticated_user($conn);
    $isPublicPreview = $authenticatedUser === null
        && filter_var($_GET['preview'] ?? false, FILTER_VALIDATE_BOOL)
        && is_public_home_preview($conn, $type, $id);

    if ($authenticatedUser === null && !$isPublicPreview) {
        http_response_code(401);
        exit('Authentication required.');
    }

    $table = $type === 'audio' ? 'audio' : 'videos';
    $idColumn = $type === 'audio' ? 'audio_id' : 'video_id';
    $nameColumn = $type === 'audio' ? 'audio_name' : 'video_name';
    $titleColumn = $type === 'audio' ? 'audio_title' : 'video_title';
    $originalColumn = $type === 'audio' ? 'audio_original_name' : 'video_original_name';
    $mimeColumn = $type === 'audio' ? 'audio_mime_type' : 'video_mime_type';

    $media = db_fetch_one(
        $conn,
        "SELECT {$nameColumn} AS stored_name,
                {$titleColumn} AS media_title,
                {$originalColumn} AS original_name,
                {$mimeColumn} AS mime_type
         FROM {$table}
         WHERE {$idColumn} = ?
         LIMIT 1",
        'i',
        [$id]
    );

    if ($media === null) {
        throw new RuntimeException('Media record not found.');
    }

    $candidateNames = media_candidate_names($media, $type);
    $privateDirectory = media_storage_directory($type);
    $legacyDirectory = ONE_SOUND_ROOT . '/admin/uploads/' . ($type === 'audio' ? 'music' : 'videos');

    $path = find_media_file($privateDirectory, $candidateNames)
        ?? find_media_file($legacyDirectory, $candidateNames);

    if ($path === null) {
        throw new RuntimeException(
            'Media file is unavailable. Restore the original files to the configured private media directory.'
        );
    }

    $size = filesize($path);
    if ($size === false || $size < 1) {
        throw new RuntimeException('Media file size is unavailable.');
    }

    $range = requested_media_range((int) $size);
    $start = $range['start'];
    $end = $range['end'];
    $length = $end - $start + 1;
    $mimeType = media_response_mime($path, $type, (string) ($media['mime_type'] ?? ''));
    $downloadName = basename((string) ($media['original_name'] ?: basename($path)));
    $asciiDownloadName = preg_replace('/[^A-Za-z0-9._-]/', '_', $downloadName) ?: ($type . '.' . ($type === 'audio' ? 'mp3' : 'mp4'));

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    ignore_user_abort(true);
    set_time_limit(0);

    http_response_code($range['status']);
    header('Content-Type: ' . $mimeType);
    header('Content-Encoding: identity');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $length);
    header('Cache-Control: ' . ($isPublicPreview ? 'public' : 'private') . ', max-age=300');
    header('Content-Disposition: inline; filename="' . $asciiDownloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));

    if ($range['status'] === 206) {
        header("Content-Range: bytes {$start}-{$end}/{$size}");
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
        exit;
    }

    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Media file could not be opened.');
    }

    try {
        if (fseek($handle, $start) !== 0) {
            throw new RuntimeException('Media stream could not seek to the requested position.');
        }

        $remaining = $length;
        while ($remaining > 0 && !feof($handle)) {
            $chunk = fread($handle, min(64 * 1024, $remaining));
            if ($chunk === false || $chunk === '') {
                break;
            }

            echo $chunk;
            $remaining -= strlen($chunk);
            flush();

            if (connection_aborted()) {
                break;
            }
        }
    } finally {
        fclose($handle);
    }

    exit;
} catch (Throwable $exception) {
    error_log('[One Sound] Media delivery error: ' . $exception->getMessage());

    if (!headers_sent()) {
        http_response_code($exception instanceof InvalidArgumentException ? 400 : 404);
    }

    exit('Media unavailable.');
}
