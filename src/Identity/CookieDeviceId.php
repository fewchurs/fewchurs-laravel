<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Identity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CookieDeviceId implements DeviceIdResolver
{
    use HashesIdentity;

    private ?string $memo = null;

    public function resolve(Request $request): string
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        $name = (string) config('fewchurs.identity.cookie.name', 'fewchurs_did');
        $raw = $request->cookie($name);

        if (! is_string($raw) || ! preg_match('/^[a-f0-9]{32}$/', $raw)) {
            $raw = bin2hex(random_bytes(16));
            $this->queue($name, $raw);
            $request->cookies->set($name, $raw);
        }

        return $this->memo = $this->hash('cookie:'.$raw);
    }

    private function queue(string $name, string $value): void
    {
        $minutes = (int) config('fewchurs.identity.cookie.lifetime_days', 365) * 24 * 60;

        Cookie::queue(Cookie::make(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: (string) config('session.path', '/'),
            domain: config('session.domain'),
            secure: (bool) config('session.secure', Str::startsWith((string) config('app.url'), 'https://')),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        ));
    }
}
