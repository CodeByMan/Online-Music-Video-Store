<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatabaseIntegrationTest extends TestCase
{
    private mysqli $connection;

    protected function setUp(): void
    {
        if (!extension_loaded('mysqli')) {
            self::markTestSkipped('mysqli is not installed.');
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->connection = new mysqli(
                getenv('DB_HOST') ?: '127.0.0.1',
                getenv('DB_USERNAME') ?: 'root',
                getenv('DB_PASSWORD') ?: '',
                getenv('DB_DATABASE') ?: 'sounds_test',
                (int) (getenv('DB_PORT') ?: 3306)
            );
        } catch (mysqli_sql_exception $exception) {
            self::markTestSkipped('Test database unavailable: ' . $exception->getMessage());
        }
        $this->connection->set_charset('utf8mb4');
        $this->connection->begin_transaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->connection)) {
            $this->connection->rollback();
            $this->connection->close();
        }
    }

    private function execute(string $sql): void
    {
        $this->connection->query($sql);
    }

    private function scalar(string $sql): int|string|null
    {
        $result = $this->connection->query($sql);
        $row = $result->fetch_row();
        return $row[0] ?? null;
    }

    private function createAudioFixture(): int
    {
        $this->execute("INSERT INTO categories (category_artist, category_album, category_genre, category_language, category_year, category_image) VALUES ('Test Artist','Test Album','Test Genre','English',2026,'test.jpg')");
        $categoryId = $this->connection->insert_id;
        $key = bin2hex(random_bytes(16)) . '.mp3';
        $this->execute("INSERT INTO audio (audio_title,audio_name,audio_original_name,audio_mime_type,audio_size_bytes,category_id) VALUES ('Test Song','{$key}','test-song.mp3','audio/mpeg',123,{$categoryId})");
        return $this->connection->insert_id;
    }

    public function testFixedRolesExist(): void
    {
        self::assertSame(2, (int) $this->scalar('SELECT COUNT(*) FROM roles WHERE role_id IN (1,2)'));
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO users (user_name,user_phone,user_address,user_email,user_password,user_image,role_id) SELECT 'Duplicate',user_phone,user_address,user_email,user_password,'vector.jpg',2 FROM users WHERE user_id=2");
    }

    public function testUnknownRoleIsRejectedByForeignKey(): void
    {
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO users (user_name,user_phone,user_address,user_email,user_password,user_image,role_id) VALUES ('Bad Role','1234567','Test','bad-role@example.test','hash','vector.jpg',99)");
    }

    public function testOneReviewPerUserAndAudio(): void
    {
        $audioId = $this->createAudioFixture();
        $this->execute("INSERT INTO review_audio (review_text,review_rating,audio_id,user_id) VALUES ('First',5,{$audioId},2)");
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO review_audio (review_text,review_rating,audio_id,user_id) VALUES ('Second',4,{$audioId},2)");
    }

    public function testRatingOutsideRangeIsRejected(): void
    {
        $audioId = $this->createAudioFixture();
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO review_audio (review_text,review_rating,audio_id,user_id) VALUES ('Invalid',6,{$audioId},2)");
    }

    public function testDeletingAudioCascadesItsReviews(): void
    {
        $audioId = $this->createAudioFixture();
        $this->execute("INSERT INTO review_audio (review_text,review_rating,audio_id,user_id) VALUES ('Review',5,{$audioId},2)");
        $this->execute("DELETE FROM audio WHERE audio_id={$audioId}");
        self::assertSame(0, (int) $this->scalar("SELECT COUNT(*) FROM review_audio WHERE audio_id={$audioId}"));
    }

    public function testCategoryYearDatabaseConstraint(): void
    {
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO categories (category_artist,category_album,category_genre,category_language,category_year,category_image) VALUES ('A','B','C','D',1800,'x.jpg')");
    }

    public function testRequestLimitKeyIsUniquePerAction(): void
    {
        $key = str_repeat('a', 64);
        $this->execute("INSERT INTO request_limits (action_name,rate_key,attempt_count,window_started_at,last_attempt_at) VALUES ('login','{$key}',1,NOW(),NOW())");
        $this->expectException(mysqli_sql_exception::class);
        $this->execute("INSERT INTO request_limits (action_name,rate_key,attempt_count,window_started_at,last_attempt_at) VALUES ('login','{$key}',1,NOW(),NOW())");
    }

    public function testMediaMetadataColumnsExist(): void
    {
        self::assertSame(10, (int) $this->scalar("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='audio' AND COLUMN_NAME IN ('audio_title','audio_original_name','audio_mime_type','audio_size_bytes','created_at')) OR (TABLE_NAME='videos' AND COLUMN_NAME IN ('video_title','video_original_name','video_mime_type','video_size_bytes','created_at')))"));
    }

    public function testMessageLifecycleColumnsExist(): void
    {
        self::assertSame(4, (int) $this->scalar("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='messages' AND COLUMN_NAME IN ('read_at','read_by','created_at','updated_at')"));
    }


    public function testUsersHaveSessionVersionForGlobalLogout(): void
    {
        self::assertSame(1, (int) $this->scalar("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='session_version' AND COLUMN_DEFAULT='1'"));
    }


    public function testDatabaseConnectSetsUtcSessionTimezone(): void
    {
        $connection = database_connect([
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'name' => getenv('DB_DATABASE') ?: 'sounds_test',
            'user' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
        ]);

        try {
            $row = db_fetch_one($connection, 'SELECT @@session.time_zone AS timezone');
            self::assertSame(DATABASE_TIMEZONE_OFFSET, $row['timezone'] ?? null);
        } finally {
            $connection->close();
        }
    }

    public function testActionRateLimiterAllowsMaximumAndBlocksNextRequest(): void
    {
        $connection = database_connect([
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'name' => getenv('DB_DATABASE') ?: 'sounds_test',
            'user' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
        ]);

        $previousRemoteAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $action = 'phpunit_action_' . bin2hex(random_bytes(8));
        $identity = 'phpunit@example.test';

        try {
            for ($attempt = 1; $attempt <= 5; $attempt++) {
                enforce_action_rate_limit($connection, $action, $identity, 5, 600, 900);
            }

            self::assertTrue(rate_limit_is_blocked($connection, $action, $identity));

            try {
                enforce_action_rate_limit($connection, $action, $identity, 5, 600, 900);
                self::fail('The request after the configured maximum should be blocked.');
            } catch (RuntimeException $exception) {
                self::assertSame('Too many requests. Please wait and try again.', $exception->getMessage());
            }
        } finally {
            rate_limit_clear($connection, $action, $identity);
            $connection->close();

            if ($previousRemoteAddress === null) {
                unset($_SERVER['REMOTE_ADDR']);
            } else {
                $_SERVER['REMOTE_ADDR'] = $previousRemoteAddress;
            }
        }
    }

}
