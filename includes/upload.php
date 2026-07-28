<?php

declare(strict_types=1);

function uploaded_file_present(array $file): bool
{
    return isset($file['error']) && (int) $file['error'] !== UPLOAD_ERR_NO_FILE;
}

function clean_original_file_name(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
    $name = trim($name);
    if ($name === '' || text_length($name) > MAX_ORIGINAL_FILE_NAME_LENGTH) {
        throw new RuntimeException('The original file name is invalid or too long.');
    }
    return $name;
}

function valid_mp3_frame_header(string $header): bool
{
    if (strlen($header) < 4 || ord($header[0]) !== 0xFF || (ord($header[1]) & 0xE0) !== 0xE0) {
        return false;
    }

    $version = (ord($header[1]) >> 3) & 0x03;
    $layer = (ord($header[1]) >> 1) & 0x03;
    $bitrateIndex = (ord($header[2]) >> 4) & 0x0F;
    $sampleRateIndex = (ord($header[2]) >> 2) & 0x03;

    return $version !== 0x01
        && $layer !== 0x00
        && $bitrateIndex !== 0x00
        && $bitrateIndex !== 0x0F
        && $sampleRateIndex !== 0x03;
}

function validate_mp3_signature(string $path): void
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('The uploaded audio file could not be inspected.');
    }

    try {
        $offset = 0;
        $tagCount = 0;

        // Some valid MP3 files contain more than one consecutive ID3 tag.
        while ($tagCount < 8) {
            if (fseek($handle, $offset) !== 0) {
                throw new RuntimeException('The MP3 metadata offset is invalid.');
            }

            $header = fread($handle, 10) ?: '';
            if (!str_starts_with($header, 'ID3')) {
                break;
            }

            if (strlen($header) < 10) {
                throw new RuntimeException('The MP3 ID3 header is incomplete.');
            }

            $sizeBytes = array_map('ord', str_split(substr($header, 6, 4)));
            foreach ($sizeBytes as $byte) {
                if (($byte & 0x80) !== 0) {
                    throw new RuntimeException('The MP3 ID3 size is invalid.');
                }
            }

            $tagSize = ($sizeBytes[0] << 21)
                | ($sizeBytes[1] << 14)
                | ($sizeBytes[2] << 7)
                | $sizeBytes[3];
            $footerSize = (ord($header[5]) & 0x10) !== 0 ? 10 : 0;
            $offset += 10 + $tagSize + $footerSize;
            $tagCount++;
        }

        if (fseek($handle, $offset) !== 0) {
            throw new RuntimeException('The MP3 audio offset is invalid.');
        }

        // Permit harmless padding before the first MPEG frame while keeping
        // inspection bounded to avoid reading the complete upload into memory.
        $scanBuffer = fread($handle, 256 * 1024) ?: '';
        $frameHeader = '';
        $bufferLength = strlen($scanBuffer);

        for ($index = 0; $index <= $bufferLength - 4; $index++) {
            $candidate = substr($scanBuffer, $index, 4);
            if (valid_mp3_frame_header($candidate)) {
                $frameHeader = $candidate;
                break;
            }
        }
    } finally {
        fclose($handle);
    }

    if (!valid_mp3_frame_header($frameHeader)) {
        throw new RuntimeException('The MP3 frame header is invalid.');
    }
}

function validate_mp4_signature(string $path): void
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        throw new RuntimeException('The uploaded video file could not be inspected.');
    }
    try {
        $header = fread($handle, 12) ?: '';
    } finally {
        fclose($handle);
    }

    $actualSize = filesize($path);
    $boxSize = strlen($header) >= 4 ? (unpack('Nsize', substr($header, 0, 4))['size'] ?? 0) : 0;
    $brand = strlen($header) >= 12 ? substr($header, 8, 4) : '';
    $allowedBrands = ['isom', 'iso2', 'iso3', 'iso4', 'iso5', 'iso6', 'mp41', 'mp42', 'avc1', 'M4V ', 'M4A ', 'dash', 'qt  ', '3gp4', '3gp5'];

    if (strlen($header) < 12
        || substr($header, 4, 4) !== 'ftyp'
        || $boxSize < 12
        || ($actualSize !== false && $boxSize > $actualSize)
        || !in_array($brand, $allowedBrands, true)) {
        throw new RuntimeException('The MP4 file header is invalid.');
    }
}

function validate_media_signature(string $path, string $type): void
{
    if ($type === 'image') {
        $dimensions = @getimagesize($path);
        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] > MAX_IMAGE_WIDTH || $dimensions[1] > MAX_IMAGE_HEIGHT) {
            throw new RuntimeException('The image dimensions are invalid or exceed 5000 × 5000 pixels.');
        }
        return;
    }

    if ($type === 'audio') {
        validate_mp3_signature($path);
        return;
    }

    if ($type === 'video') {
        validate_mp4_signature($path);
        return;
    }

    throw new InvalidArgumentException('Unsupported signature validation type.');
}

function store_uploaded_file(array $file, string $targetDirectory, string $type, string $fileNamePrefix = ''): array
{
    $rules = [
        'image' => [
            'max' => 5 * 1024 * 1024,
            'mimes' => [
                'image/jpeg' => ['stored_extension' => 'jpg', 'allowed_extensions' => ['jpg', 'jpeg']],
                'image/png' => ['stored_extension' => 'png', 'allowed_extensions' => ['png']],
                'image/webp' => ['stored_extension' => 'webp', 'allowed_extensions' => ['webp']],
            ],
        ],
        'audio' => [
            'max' => 25 * 1024 * 1024,
            'mimes' => [
                'audio/mpeg' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
                'audio/mp3' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
                'audio/mpeg3' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
                'audio/x-mpeg' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
                'audio/x-mp3' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
                'audio/x-mpeg-3' => ['stored_extension' => 'mp3', 'allowed_extensions' => ['mp3']],
            ],
        ],
        'video' => [
            'max' => 100 * 1024 * 1024,
            'mimes' => [
                'video/mp4' => ['stored_extension' => 'mp4', 'allowed_extensions' => ['mp4']],
                'application/mp4' => ['stored_extension' => 'mp4', 'allowed_extensions' => ['mp4']],
            ],
        ],
    ];
    if (!isset($rules[$type])) {
        throw new InvalidArgumentException('Unsupported upload type.');
    }

    if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The selected file could not be uploaded.');
    }

    $temporaryPath = $file['tmp_name'] ?? '';
    $originalName = clean_original_file_name((string) ($file['name'] ?? ''));
    if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
        throw new RuntimeException('The uploaded file is invalid.');
    }

    $actualSize = filesize($temporaryPath);
    if ($actualSize === false || $actualSize < 1 || $actualSize > $rules[$type]['max']) {
        throw new RuntimeException('The uploaded file exceeds the allowed size.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    $mimeRule = is_string($mime) ? ($rules[$type]['mimes'][$mime] ?? null) : null;
    if ($mimeRule === null) {
        throw new RuntimeException('The uploaded file type is not allowed.');
    }

    $originalExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($originalExtension, $mimeRule['allowed_extensions'], true)) {
        throw new RuntimeException('The file extension does not match the uploaded file type.');
    }
    $title = trim(pathinfo($originalName, PATHINFO_FILENAME));
    if ($title === '') {
        throw new RuntimeException('The uploaded file must have a readable filename.');
    }
    validate_media_signature($temporaryPath, $type);

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('The upload directory is unavailable.');
    }

    $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $fileNamePrefix) ?? '';
    $storedName = $safePrefix . bin2hex(random_bytes(16)) . '.' . $mimeRule['stored_extension'];
    $targetPath = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $storedName;
    if (!move_uploaded_file($temporaryPath, $targetPath)) {
        throw new RuntimeException('The uploaded file could not be saved.');
    }
    @chmod($targetPath, 0640);

    return [
        'stored_name' => $storedName,
        'original_name' => $originalName,
        'title' => $title,
        'mime_type' => (string) $mime,
        'size' => (int) $actualSize,
    ];
}

function delete_file_if_safe(string $directory, ?string $fileName, ?string $requiredPrefix = null): void
{
    if ($fileName === null || $fileName === '' || $fileName === 'vector.jpg') {
        return;
    }
    $safeName = basename($fileName);
    if ($requiredPrefix !== null && !str_starts_with($safeName, $requiredPrefix)) {
        return;
    }
    $base = realpath($directory);
    if ($base === false) {
        return;
    }
    $candidate = $base . DIRECTORY_SEPARATOR . $safeName;
    if (is_file($candidate) && !unlink($candidate)) {
        error_log('[One Sound] Unable to delete media file: ' . $candidate);
    }
}

function media_storage_directory(string $type): string
{
    global $appConfig;
    if (!in_array($type, ['audio', 'video'], true)) {
        throw new InvalidArgumentException('Unsupported media type.');
    }
    return rtrim((string) $appConfig['media_storage_path'], '/\\') . DIRECTORY_SEPARATOR . $type;
}
