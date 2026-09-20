<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Identity;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;

final class CallbackDeviceId implements DeviceIdResolver
{
    public function __construct(private readonly Closure $callback) {}

    public function resolve(Request $request): string
    {
        $value = ($this->callback)($request);

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException('The Fewchurs device-id callback must return a non-empty string.');
        }

        if (str_contains($value, '@')) {
            throw new RuntimeException('A Fewchurs device id must be hashed — never an email or a raw identifier.');
        }

        return $value;
    }
}
