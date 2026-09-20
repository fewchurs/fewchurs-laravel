<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Support;

final class Redactor
{
    public const PLACEHOLDER = '[redacted]';

    private const PATTERNS = [
        '/\bfr_(?:live|test)_[A-Za-z0-9_\-]+/',
        '/\b(?i:bearer)\s+[A-Za-z0-9._\-]+/',
    ];

    public static function scrub(string $value): string
    {
        return (string) preg_replace(self::PATTERNS, self::PLACEHOLDER, $value);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function scrubContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                $context[$key] = self::scrub($value);
            } elseif (is_array($value)) {
                $context[$key] = self::scrubContext($value);
            }
        }

        return $context;
    }

    public static function mask(?string $key): string
    {
        if ($key === null || $key === '') {
            return self::PLACEHOLDER;
        }

        $visible = substr($key, 0, 8);

        return $visible.str_repeat('*', max(4, min(16, strlen($key) - 8)));
    }
}
