<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Support;

use Fewchurs\Laravel\Exceptions\NotFoundException;

/**
 * Ids arriving from the browser are interpolated into upstream paths, so they are
 * constrained to Firestore's document-id alphabet before they ever reach a URL.
 */
final class Ids
{
    private const PATTERN = '/^[A-Za-z0-9_-]{1,128}$/';

    public static function isValid(string $id): bool
    {
        return preg_match(self::PATTERN, $id) === 1;
    }

    public static function assert(string $id): string
    {
        if (! self::isValid($id)) {
            throw new NotFoundException('Invalid resource id.', 'not_found', 404);
        }

        return $id;
    }
}
