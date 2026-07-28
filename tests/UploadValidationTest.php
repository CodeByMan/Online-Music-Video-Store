<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UploadValidationTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }
    }

    private function temporaryFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'one-sound-test-');
        self::assertNotFalse($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;
        return $path;
    }

    public function testOriginalFileNameRejectsTraversalAndControlCharactersSafely(): void
    {
        self::assertSame('song.mp3', clean_original_file_name('../../song.mp3'));
    }

    public function testExcessivelyLongOriginalFileNameIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        clean_original_file_name(str_repeat('a', 181) . '.mp3');
    }

    public function testValidMp3SignatureIsAccepted(): void
    {
        validate_media_signature($this->temporaryFile("ID3\x04\x00\x00\x00\x00\x00\x00\xFF\xFB\x90\x64" . str_repeat("\0", 20)), 'audio');
        self::assertTrue(true);
    }

    public function testConsecutiveId3TagsBeforeAudioFrameAreAccepted(): void
    {
        $firstTag = "ID3\x03\x00\x00\x00\x00\x00\x00";
        $secondTag = "ID3\x04\x00\x00\x00\x00\x00\x00";
        $frame = "\xFF\xFB\x90\x64" . str_repeat("\0", 20);

        validate_media_signature($this->temporaryFile($firstTag . $secondTag . $frame), 'audio');
        self::assertTrue(true);
    }

    public function testInvalidMp3SignatureIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        validate_media_signature($this->temporaryFile('not-an-mp3'), 'audio');
    }

    public function testValidMp4SignatureIsAccepted(): void
    {
        validate_media_signature($this->temporaryFile("\0\0\0\x18ftypisom" . str_repeat("\0", 12)), 'video');
        self::assertTrue(true);
    }


    public function testValidRawMp3FrameIsAccepted(): void
    {
        validate_media_signature($this->temporaryFile("\xFF\xFB\x90\x64" . str_repeat("\0", 20)), 'audio');
        self::assertTrue(true);
    }

    public function testId3TagWithoutAudioFrameIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        validate_media_signature($this->temporaryFile("ID3\x04\x00\x00\x00\x00\x00\x00" . str_repeat("\0", 20)), 'audio');
    }

    public function testUnknownMp4BrandIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        validate_media_signature($this->temporaryFile("\0\0\0\x18ftypxxxx" . str_repeat("\0", 12)), 'video');
    }

    public function testInvalidMp4SignatureIsRejected(): void
    {
        $this->expectException(RuntimeException::class);
        validate_media_signature($this->temporaryFile('not-an-mp4'), 'video');
    }
}
