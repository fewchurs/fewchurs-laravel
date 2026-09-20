<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Validation;

/**
 * Single source of truth for the server's limits: the Form Requests validate with
 * these and the Blade views render maxlength from them, so the two cannot drift.
 */
final class Rules
{
    public const TITLE_MIN = 3;

    public const TITLE_MAX = 100;

    public const DESCRIPTION_MAX = 1000;

    public const COMMENT_MIN = 1;

    public const COMMENT_MAX = 1000;

    public const AUTHOR_NAME_MAX = 60;

    public const EMAIL_MAX = 254;

    /**
     * @return array<int, string>
     */
    public static function title(): array
    {
        return ['required', 'string', 'min:'.self::TITLE_MIN, 'max:'.self::TITLE_MAX];
    }

    /**
     * @return array<int, string>
     */
    public static function description(): array
    {
        return ['nullable', 'string', 'max:'.self::DESCRIPTION_MAX];
    }

    /**
     * @return array<int, string>
     */
    public static function commentText(): array
    {
        return ['required', 'string', 'min:'.self::COMMENT_MIN, 'max:'.self::COMMENT_MAX];
    }

    /**
     * @return array<int, string>
     */
    public static function authorName(): array
    {
        return ['nullable', 'string', 'max:'.self::AUTHOR_NAME_MAX];
    }

    /**
     * @return array<int, string>
     */
    public static function email(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'email:filter', 'max:'.self::EMAIL_MAX];
    }
}
