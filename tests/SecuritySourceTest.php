<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecuritySourceTest extends TestCase
{
    private function source(string $relativePath): string
    {
        $contents = file_get_contents(ONE_SOUND_ROOT . '/' . $relativePath);
        self::assertNotFalse($contents, 'Unable to read ' . $relativePath);
        return $contents;
    }

    public function testAdminDeleteRequiresPostCsrfAndAdministrator(): void
    {
        $source = $this->source('admin/delete.php');
        self::assertStringContainsString('require_admin($conn)', $source);
        self::assertStringContainsString('require_post_request()', $source);
        self::assertStringContainsString('verify_csrf()', $source);
    }

    public function testMediaEndpointLimitsPublicAccessToHomepagePreviews(): void
    {
        $mediaSource = $this->source('frontend/media.php');
        self::assertStringContainsString('authenticated_user($conn)', $mediaSource);
        self::assertStringContainsString('is_public_home_preview($conn, $type, $id)', $mediaSource);
        self::assertStringContainsString("exit('Authentication required.')", $mediaSource);

        $homeSource = $this->source('frontend/partials/home-page.php');
        self::assertStringContainsString("\$previewParameter = \$homeAuthenticated ? '' : '&amp;preview=1'", $homeSource);
        self::assertStringContainsString("\$musicMoreText = \$homeAuthenticated ? 'See More Music' : 'Login For More'", $homeSource);
        self::assertStringContainsString('Music is like a magical sound party', $homeSource);
        self::assertStringContainsString('home-trending-field', $homeSource);
        self::assertStringContainsString('home-trending-player', $homeSource);
    }


    public function testPublicAudioPreviewMatchesFourUniqueHomepageTracks(): void
    {
        $mediaSource = $this->source('frontend/media.php');
        self::assertStringContainsString('$limit = $type === \'audio\' ? 4 : 6;', $mediaSource);
    }

    public function testActionRateLimitAllowsTheConfiguredFinalRequest(): void
    {
        $source = $this->source('includes/auth.php');
        $functionStart = strpos($source, 'function enforce_action_rate_limit');
        self::assertNotFalse($functionStart);
        $functionSource = substr($source, $functionStart, 900);

        self::assertSame(1, substr_count($functionSource, 'rate_limit_is_blocked('));
        self::assertStringContainsString('rate_limit_hit(', $functionSource);
    }

    public function testSessionAndDatabaseTimePoliciesAreExplicit(): void
    {
        self::assertStringContainsString(
            "ini_set('session.gc_maxlifetime', (string) AUTH_IDLE_TIMEOUT)",
            $this->source('includes/session.php')
        );
        self::assertStringContainsString(
            'date_default_timezone_set(APPLICATION_TIMEZONE)',
            $this->source('includes/bootstrap.php')
        );
        self::assertStringContainsString(
            'DATABASE_TIMEZONE_OFFSET',
            $this->source('includes/database.php')
        );
        self::assertStringContainsString(
            "const APPLICATION_TIMEZONE = 'UTC';",
            $this->source('includes/config.php')
        );
        self::assertStringContainsString(
            "const DATABASE_TIMEZONE_OFFSET = '+00:00';",
            $this->source('includes/config.php')
        );
    }


    public function testMediaDeliverySupportsRestoredOriginalNamesAndRanges(): void
    {
        $mediaSource = $this->source('frontend/media.php');
        self::assertStringContainsString('media_candidate_names', $mediaSource);
        self::assertStringContainsString('find_media_file', $mediaSource);
        self::assertStringContainsString('requested_media_range', $mediaSource);
        self::assertStringContainsString("header('Accept-Ranges: bytes')", $mediaSource);
        self::assertStringContainsString('ob_end_clean()', $mediaSource);
    }

    public function testSelfHostedVideoPopupIsAllowedByCsp(): void
    {
        $source = $this->source('includes/bootstrap.php');
        self::assertStringContainsString("frame-src 'self' https://maps.google.com https://www.google.com", $source);
    }

    public function testPrivateMediaRestorationUtilitiesExist(): void
    {
        self::assertFileExists(ONE_SOUND_ROOT . '/scripts/restore_private_media.php');
        self::assertFileExists(ONE_SOUND_ROOT . '/scripts/restore_private_media.ps1');
    }

    public function testPasswordChangesVerifyCurrentPassword(): void
    {
        foreach (['admin/update_password.php', 'frontend/update_password.php'] as $file) {
            self::assertStringContainsString('password_verify($currentPassword', $this->source($file));
        }
    }

    public function testReviewMutationsContainOwnershipCondition(): void
    {
        self::assertStringContainsString('user_id = ?', $this->source('frontend/edit_review.php'));
        self::assertStringContainsString('user_id = ?', $this->source('frontend/delete_review.php'));
    }

    public function testNoApplicationPageUsesRawMysqliQuery(): void
    {
        foreach (['admin', 'frontend'] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ONE_SOUND_ROOT . '/' . $directory));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    self::assertStringNotContainsString('mysqli_query(', file_get_contents($file->getPathname()), $file->getPathname());
                }
            }
        }
    }

    public function testCatalogDoesNotLinkLegacyPublicMediaDirectories(): void
    {
        foreach (['admin', 'frontend'] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ONE_SOUND_ROOT . '/' . $directory));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $source = file_get_contents($file->getPathname());
                    self::assertDoesNotMatchRegularExpression('~(?:src|href)=["\'][^"\']*uploads/(?:music|videos)/~', $source, $file->getPathname());
                }
            }
        }
    }

    public function testRoleCrudEndpointsWereRemoved(): void
    {
        self::assertFileDoesNotExist(ONE_SOUND_ROOT . '/admin/add_role.php');
        self::assertFileDoesNotExist(ONE_SOUND_ROOT . '/admin/edit_role.php');
    }

    public function testDeadTemplateContactScriptsWereRemoved(): void
    {
        foreach (['contact.js', 'mail-script.js', 'ajax-form.js', 'jquery.form.js', 'jquery.validate.min.js'] as $file) {
            self::assertFileDoesNotExist(ONE_SOUND_ROOT . '/frontend/js/' . $file);
        }
    }


    public function testFrontendAuthorizationRequiresExactUserRole(): void
    {
        $source = $this->source('includes/auth.php');
        self::assertStringContainsString('(int) $user[\'role_id\'] !== ROLE_USER', $source);
    }

    public function testPasswordChangeRevokesExistingSessions(): void
    {
        self::assertStringContainsString('session_version', $this->source('database/sounds.sql'));
        self::assertStringContainsString("session_version = session_version + 1", $this->source('admin/update_password.php'));
        self::assertStringContainsString("session_version = session_version + 1", $this->source('frontend/update_password.php'));
        self::assertStringContainsString('(int) $user[\'session_version\'] !== (int) $sessionVersion', $this->source('includes/auth.php'));
    }

    public function testLegacyMediaMigrationValidatesSignatures(): void
    {
        self::assertStringContainsString('validate_media_signature($legacyPath, $type)', $this->source('scripts/migrate.php'));
        self::assertStringContainsString("\$summary['invalid']", $this->source('scripts/migrate.php'));
    }

    public function testSecurityHeadersIncludeCspAndConditionalHsts(): void
    {
        $source = $this->source('includes/bootstrap.php');
        self::assertStringContainsString('Content-Security-Policy:', $source);
        self::assertStringContainsString('Strict-Transport-Security:', $source);
        self::assertStringContainsString('request_is_https()', $source);
    }

    public function testPortablePhpLinterExists(): void
    {
        self::assertFileExists(ONE_SOUND_ROOT . '/scripts/lint.php');
        $composer = json_decode($this->source('composer.json'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('php scripts/lint.php', $composer['scripts']['lint'] ?? null);
    }

    public function testCompiledBootstrapRuntimeAssetExists(): void
    {
        self::assertFileExists(ONE_SOUND_ROOT . '/admin/vendor/bootstrap/js/bootstrap.bundle.min.js');
    }

}
