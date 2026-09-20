<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

final class Honeypot
{
    public static function enabled(): bool
    {
        return (bool) config('fewchurs.route.honeypot', true);
    }

    public static function field(): string
    {
        return (string) config('fewchurs.honeypot.field', 'fewchurs_hp');
    }

    public static function timestampField(): string
    {
        return (string) config('fewchurs.honeypot.timestamp_field', 'fewchurs_ts');
    }

    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }

    public static function passes(?string $decoy, ?string $token): bool
    {
        if (is_string($decoy) && $decoy !== '') {
            return false;
        }

        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $issuedAt = (int) Crypt::decryptString($token);
        } catch (DecryptException) {
            return false;
        }

        $age = time() - $issuedAt;

        return $age >= (int) config('fewchurs.honeypot.min_seconds', 2)
            && $age <= (int) config('fewchurs.honeypot.max_seconds', 10800);
    }
}
