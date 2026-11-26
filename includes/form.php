<?php
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}
function input(string $key, bool $trim = true): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}
function validDateInput(string $value): bool
{
    // Reject malformed bytes before PHP's date parser, which can throw on null bytes.
    if (
        strlen($value) !== 10 ||
        $value[4] !== '-' ||
        $value[7] !== '-' ||
        !ctype_digit(str_replace('-', '', $value))
    ) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function validPhone(string $value): bool
{
    if (strlen($value) === 11 && str_starts_with($value, '09')) {
        return strspn($value, '0123456789') === 11;
    }
    return strlen($value) === 13 && str_starts_with($value, '+639')
        && strspn($value, '0123456789', 1) === 12;
}
