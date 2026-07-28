<?php

declare(strict_types=1);

function request_positive_int(array $source, string $key): int
{
    $raw = $source[$key] ?? null;
    if (is_array($raw)) {
        throw new InvalidArgumentException('Invalid identifier.');
    }

    $value = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($value === false) {
        throw new InvalidArgumentException('Invalid identifier.');
    }
    return (int) $value;
}

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function input_text(array $source, string $key, int $minLength, int $maxLength, bool $preserveLineBreaks = false): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Invalid input.');
    }

    $value = trim($value);
    if (!$preserveLineBreaks) {
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    $length = text_length($value);
    if ($length < $minLength || $length > $maxLength) {
        throw new InvalidArgumentException(sprintf('%s must be between %d and %d characters.', ucfirst(str_replace('_', ' ', $key)), $minLength, $maxLength));
    }
    return $value;
}

function optional_input_text(array $source, string $key, int $maxLength): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Invalid input.');
    }
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    if (text_length($value) > $maxLength) {
        throw new InvalidArgumentException(sprintf('%s must not exceed %d characters.', ucfirst(str_replace('_', ' ', $key)), $maxLength));
    }
    return $value;
}

function input_email(array $source, string $key = 'email'): string
{
    $email = strtolower(input_text($source, $key, 5, 254));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please enter a valid email address.');
    }
    return $email;
}

function input_phone(array $source, string $key): string
{
    $phone = input_text($source, $key, 7, 30);
    if (!preg_match('/^[0-9+()\-\s]+$/', $phone) || !preg_match('/\d/', $phone)) {
        throw new InvalidArgumentException('Please enter a valid phone number containing at least one digit.');
    }
    return $phone;
}

function input_password(array $source, string $key): string
{
    $password = $source[$key] ?? '';
    if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
        throw new InvalidArgumentException('Password must be between 8 and 72 characters.');
    }
    return $password;
}

function input_year(array $source, string $key): int
{
    $year = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1900, 'max_range' => (int) date('Y') + 1],
    ]);
    if ($year === false) {
        throw new InvalidArgumentException('Please enter a valid year.');
    }
    return (int) $year;
}

function input_search(array $source, string $key = 'q', int $maxLength = 100): string
{
    $value = $source[$key] ?? '';
    if (!is_string($value)) {
        throw new InvalidArgumentException('Please enter a valid search term.');
    }
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    if ($value !== '' && text_length($value) > $maxLength) {
        throw new InvalidArgumentException('Search term is too long.');
    }
    return $value;
}
