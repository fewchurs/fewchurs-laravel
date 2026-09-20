<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Support;

/**
 * Theme values are inlined into a style attribute, so only a strict allowlist of
 * colour and length syntax is accepted; anything else falls back to the default.
 */
final class Theme
{
    private const DEFAULTS = [
        'primary' => '#ED5F18',
        'radius' => '0.75rem',
    ];

    private const PATTERNS = [
        'primary' => '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/',
        'radius' => '/^\d{1,3}(?:\.\d{1,3})?(?:px|rem|em|%)$/',
    ];

    /**
     * @param  array<string, mixed>|null  $overrides
     */
    public static function styles(?array $overrides = null): string
    {
        $config = (array) config('fewchurs.theme', []);
        $styles = [];

        foreach (self::DEFAULTS as $key => $default) {
            $value = $overrides[$key] ?? $config[$key] ?? $default;
            $value = is_string($value) && preg_match(self::PATTERNS[$key], $value) === 1 ? $value : $default;
            $styles[] = "--fewchurs-{$key}: {$value}";
        }

        return implode('; ', $styles).';';
    }
}
