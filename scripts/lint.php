<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$excludedDirectories = ['.git', 'vendor', 'node_modules', '.phpunit.cache'];
$failures = [];
$count = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function (SplFileInfo $current) use ($excludedDirectories): bool {
            return !$current->isDir() || !in_array($current->getFilename(), $excludedDirectories, true);
        }
    )
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $count++;
    $path = $file->getPathname();
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);
    if ($exitCode !== 0) {
        $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
        $failures[] = $relative . ': ' . implode(PHP_EOL, $output);
    }
}

if ($failures !== []) {
    fwrite(STDERR, "PHP lint failed:\n- " . implode("\n- ", $failures) . PHP_EOL);
    exit(1);
}

echo "PHP lint passed for {$count} files." . PHP_EOL;
