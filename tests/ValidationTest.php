<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    public function testValidEmailIsNormalized(): void
    {
        self::assertSame('demo@example.test', input_email(['email' => ' Demo@Example.Test ']));
    }

    #[DataProvider('invalidEmailProvider')]
    public function testInvalidEmailIsRejected(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_email(['email' => $value]);
    }

    public static function invalidEmailProvider(): array
    {
        return [['invalid'], ['a@'], [[]]];
    }

    public function testPhoneRequiresAtLeastOneDigit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_phone(['phone' => '++++---'], 'phone');
    }

    public function testFormattedPhoneIsAccepted(): void
    {
        self::assertSame('+92 (300) 123-4567', input_phone(['phone' => '+92 (300) 123-4567'], 'phone'));
    }

    public function testMalformedSearchArrayIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_search(['q' => ['unexpected']]);
    }

    public function testOverlongSearchIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_search(['q' => str_repeat('a', 101)]);
    }

    public function testWhitespaceIsNormalized(): void
    {
        self::assertSame('One Sound Project', input_text(['name' => " One\n Sound   Project "], 'name', 2, 100));
    }

    public function testPositiveIntegerIsAccepted(): void
    {
        self::assertSame(15, request_positive_int(['id' => '15'], 'id'));
    }

    public function testInvalidIdentifierIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        request_positive_int(['id' => '../1'], 'id');
    }

    public function testShortPasswordIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_password(['password' => 'short'], 'password');
    }

    public function testValidPasswordIsAccepted(): void
    {
        self::assertSame('StrongPass123!', input_password(['password' => 'StrongPass123!'], 'password'));
    }

    public function testImpossibleYearIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        input_year(['year' => '1800'], 'year');
    }
}
