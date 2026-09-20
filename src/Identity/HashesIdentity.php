<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Identity;

use RuntimeException;

trait HashesIdentity
{
    /** Nothing raw ever leaves the app: every identity is an app-key HMAC. */
    protected function hash(string $value): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new RuntimeException('APP_KEY must be set before Fewchurs can resolve a device id.');
        }

        if (str_starts_with($key, 'base64:')) {
            $key = (string) base64_decode(substr($key, 7), true);
        }

        return hash_hmac('sha256', $value, $key);
    }
}
