<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$verifiedAssets = [];

/**
 * Convert filesystem paths to consistent forward-slash notation.
 */
function slashPath(string $path): string
{
    return str_replace('\\', '/', $path);
}

/**
 * Return a readable project-relative path.
 */
function relativePath(string $path, string $root): string
{
    $normalizedPath = slashPath($path);
    $normalizedRoot = rtrim(slashPath($root), '/');

    if (str_starts_with($normalizedPath, $normalizedRoot . '/')) {
        return substr($normalizedPath, strlen($normalizedRoot) + 1);
    }

    return $normalizedPath;
}

/**
 * Determine whether a URL is external, dynamic or not a filesystem asset.
 */
function shouldSkipReference(string $reference): bool
{
    $reference = trim($reference);

    if ($reference === '' || $reference === '#') {
        return true;
    }

    if (
        str_contains($reference, '<?')
        || str_contains($reference, '?>')
        || str_contains($reference, '{{')
        || str_contains($reference, '}}')
    ) {
        return true;
    }

    if (
        str_starts_with($reference, '#')
        || str_starts_with($reference, '//')
    ) {
        return true;
    }

    return preg_match(
        '~^(?:https?:|mailto:|tel:|javascript:|data:|blob:)~i',
        $reference
    ) === 1;
}

/**
 * Remove query strings and fragments from a local asset reference.
 */
function cleanReference(string $reference): ?string
{
    $reference = html_entity_decode(
        trim($reference),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    if (shouldSkipReference($reference)) {
        return null;
    }

    $reference = preg_split('/[?#]/', $reference, 2)[0] ?? '';
    $reference = rawurldecode(trim($reference));
    $reference = str_replace('\\', '/', $reference);

    if ($reference === '') {
        return null;
    }

    return $reference;
}

/**
 * PHP templates inside frontend/partials still resolve browser assets from
 * frontend/. The same policy applies to all frontend and admin PHP pages.
 */
function templateBaseDirectory(string $file, string $root): string
{
    $relative = relativePath($file, $root);

    if (
        $relative === 'frontend'
        || str_starts_with($relative, 'frontend/')
    ) {
        return $root . DIRECTORY_SEPARATOR . 'frontend';
    }

    if (
        $relative === 'admin'
        || str_starts_with($relative, 'admin/')
    ) {
        return $root . DIRECTORY_SEPARATOR . 'admin';
    }

    return dirname($file);
}

/**
 * Resolve a project-local URL to its expected filesystem location.
 */
function resolveLocalReference(
    string $reference,
    string $baseDirectory,
    string $root
): ?string {
    $reference = cleanReference($reference);

    if ($reference === null) {
        return null;
    }

    /*
     * Support root-relative application URLs, including deployments such as:
     * /One%20Sound/frontend/css/style.css
     */
    if (str_starts_with($reference, '/')) {
        $reference = ltrim($reference, '/');
        $segments = explode('/', $reference);

        foreach (['frontend', 'admin'] as $applicationArea) {
            $index = array_search(
                $applicationArea,
                $segments,
                true
            );

            if ($index !== false) {
                $reference = implode(
                    '/',
                    array_slice($segments, (int) $index)
                );

                return $root
                    . DIRECTORY_SEPARATOR
                    . str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $reference
                    );
            }
        }

        /*
         * Unknown web-server root URL. It may be outside this project,
         * so do not report it as a missing repository asset.
         */
        return null;
    }

    return rtrim($baseDirectory, '/\\')
        . DIRECTORY_SEPARATOR
        . str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $reference
        );
}

/**
 * Register and verify a local asset.
 */
function verifyReference(
    string $reference,
    string $sourceFile,
    string $baseDirectory,
    string $root,
    array &$failures,
    array &$verifiedAssets
): void {
    $candidate = resolveLocalReference(
        $reference,
        $baseDirectory,
        $root
    );

    if ($candidate === null) {
        return;
    }

    if (!is_file($candidate)) {
        $failures[] = sprintf(
            'Missing local asset referenced by %s: %s',
            relativePath($sourceFile, $root),
            $reference
        );

        return;
    }

    $realCandidate = realpath($candidate);

    $verifiedAssets[] = $realCandidate !== false
        ? slashPath($realCandidate)
        : slashPath($candidate);
}

/**
 * Recursively collect files with selected extensions.
 *
 * @param string[] $extensions
 * @return string[]
 */
function collectFiles(
    string $directory,
    array $extensions,
    array $excludedDirectoryNames = []
): array {
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $item) {
        if (!$item->isFile()) {
            continue;
        }

        $path = $item->getPathname();
        $normalized = '/' . trim(slashPath($path), '/') . '/';

        $excluded = false;

        foreach ($excludedDirectoryNames as $excludedName) {
            if (
                str_contains(
                    $normalized,
                    '/' . trim($excludedName, '/') . '/'
                )
            ) {
                $excluded = true;
                break;
            }
        }

        if ($excluded) {
            continue;
        }

        $extension = strtolower(
            pathinfo($path, PATHINFO_EXTENSION)
        );

        if (in_array($extension, $extensions, true)) {
            $files[] = $path;
        }
    }

    sort($files);

    return $files;
}

/*
|--------------------------------------------------------------------------
| Required release files
|--------------------------------------------------------------------------
*/

$requiredFiles = [
    '.gitignore',
    '.gitattributes',
    'LICENSE',
    'README.md',
    'composer.json',
    'composer.lock',
    'config.example.php',
    'phpunit.xml.dist',
    'database/sounds.sql',
    'scripts/lint.php',
    'scripts/check_repository.php',
    'scripts/migrate.php',
    'tests/DatabaseIntegrationTest.php',
    'frontend/home.php',
    'frontend/index.php',
    'frontend/media.php',
    'frontend/header.php',
    'frontend/footer.php',
    'frontend/partials/home-page.php',
    'frontend/partials/public-header.php',
    'frontend/partials/public-footer.php',
    'admin/login.php',
    'admin/header.php',
    'admin/footer.php',
];

foreach ($requiredFiles as $requiredFile) {
    $requiredPath = $root
        . DIRECTORY_SEPARATOR
        . str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $requiredFile
        );

    if (!is_file($requiredPath)) {
        $failures[] = 'Required release file is missing: '
            . $requiredFile;
    }
}

/*
|--------------------------------------------------------------------------
| Obsolete files that must stay removed
|--------------------------------------------------------------------------
*/

$forbiddenPaths = [
    'admin/add_role.php',
    'admin/edit_role.php',
    'admin/gulpfile.js',
    'admin/package.json',
    'admin/package-lock.json',
    'admin/scss',
    'admin/js/demo/chart-area-demo.js',
    'admin/js/demo/chart-bar-demo.js',
    'admin/js/demo/chart-pie-demo.js',
    'admin/js/demo/datatables-demo.js',
    'frontend/greeting.php',
    'frontend/js/contact.js',
    'frontend/js/mail-script.js',
    'frontend/js/ajax-form.js',
    'frontend/js/jquery.form.js',
    'frontend/js/jquery.validate.min.js',
    'frontend/js/jquery.ajaxchimp.min.js',
    'frontend/js/vendor/jquery-1.12.4.min.js',
    'frontend/css/theme-default.css',
    'frontend/css/theme-default.map',
    'frontend/css/style.map',
    'frontend/uploads',
    'database/backend_hardening.sql',
    'THIRD_PARTY_NOTICES.md',
];

foreach ($forbiddenPaths as $forbiddenPath) {
    $fullPath = $root
        . DIRECTORY_SEPARATOR
        . str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $forbiddenPath
        );

    if (file_exists($fullPath)) {
        $failures[] = 'Forbidden or obsolete path is present: '
            . $forbiddenPath;
    }
}

/*
|--------------------------------------------------------------------------
| Local configuration ignore policy
|--------------------------------------------------------------------------
*/

$gitIgnorePath = $root . DIRECTORY_SEPARATOR . '.gitignore';
$gitIgnore = is_file($gitIgnorePath)
    ? file_get_contents($gitIgnorePath)
    : false;

if ($gitIgnore === false) {
    $failures[] = 'Unable to read .gitignore.';
} else {
    if (
        preg_match(
            '/^\/?config\.local\.php\s*$/m',
            $gitIgnore
        ) !== 1
    ) {
        $failures[] = '.gitignore must exclude config.local.php.';
    }

    if (
        preg_match(
            '/^\/vendor\/?\s*$/m',
            $gitIgnore
        ) !== 1
    ) {
        $failures[] = '.gitignore must exclude the root vendor directory.';
    }
}

/*
|--------------------------------------------------------------------------
| PHP and HTML asset references
|--------------------------------------------------------------------------
*/

$templateFiles = array_merge(
    collectFiles(
        $root . DIRECTORY_SEPARATOR . 'frontend',
        ['php', 'html', 'htm'],
        ['vendor']
    ),
    collectFiles(
        $root . DIRECTORY_SEPARATOR . 'admin',
        ['php', 'html', 'htm'],
        ['vendor']
    )
);

foreach ($templateFiles as $templateFile) {
    $source = file_get_contents($templateFile);

    if ($source === false) {
        $failures[] = 'Unable to read source file: '
            . relativePath($templateFile, $root);
        continue;
    }

    preg_match_all(
        '/\b(?:src|href)\s*=\s*(["\'])(.*?)\1/is',
        $source,
        $attributeMatches,
        PREG_SET_ORDER
    );

    $baseDirectory = templateBaseDirectory(
        $templateFile,
        $root
    );

    foreach ($attributeMatches as $attributeMatch) {
        verifyReference(
            $attributeMatch[2],
            $templateFile,
            $baseDirectory,
            $root,
            $failures,
            $verifiedAssets
        );
    }
}

/*
|--------------------------------------------------------------------------
| Application CSS asset references
|--------------------------------------------------------------------------
*/

$cssDirectories = [
    $root . DIRECTORY_SEPARATOR . 'frontend'
        . DIRECTORY_SEPARATOR . 'css',
    $root . DIRECTORY_SEPARATOR . 'frontend'
        . DIRECTORY_SEPARATOR . 'fonts',
    $root . DIRECTORY_SEPARATOR . 'admin'
        . DIRECTORY_SEPARATOR . 'css',
];

$cssFiles = [];

foreach ($cssDirectories as $cssDirectory) {
    $cssFiles = array_merge(
        $cssFiles,
        collectFiles(
            $cssDirectory,
            ['css'],
            ['vendor']
        )
    );
}

$cssFiles = array_values(array_unique($cssFiles));
sort($cssFiles);

foreach ($cssFiles as $cssFile) {
    $source = file_get_contents($cssFile);

    if ($source === false) {
        $failures[] = 'Unable to read CSS file: '
            . relativePath($cssFile, $root);
        continue;
    }

    preg_match_all(
        '/url\(\s*(["\']?)(.*?)\1\s*\)/i',
        $source,
        $urlMatches,
        PREG_SET_ORDER
    );

    foreach ($urlMatches as $urlMatch) {
        verifyReference(
            $urlMatch[2],
            $cssFile,
            dirname($cssFile),
            $root,
            $failures,
            $verifiedAssets
        );
    }
}

/*
|--------------------------------------------------------------------------
| Final result
|--------------------------------------------------------------------------
*/

$failures = array_values(array_unique($failures));
sort($failures);

$verifiedAssets = array_values(
    array_unique($verifiedAssets)
);
sort($verifiedAssets);

if ($failures !== []) {
    fwrite(
        STDERR,
        "Repository checks failed:\n"
    );

    foreach ($failures as $failure) {
        fwrite(
            STDERR,
            '- ' . $failure . PHP_EOL
        );
    }

    exit(1);
}

echo sprintf(
    "Repository checks passed. Verified %d local asset references.\n",
    count($verifiedAssets)
);