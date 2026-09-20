<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Identity;

use Illuminate\Http\Request;

class UserDeviceId implements DeviceIdResolver
{
    use HashesIdentity;

    public function __construct(private readonly CookieDeviceId $fallback) {}

    public function resolve(Request $request): string
    {
        $user = $request->user();

        if ($user === null) {
            return $this->fallback->resolve($request);
        }

        return $this->hash('user:'.$user->getAuthIdentifier());
    }
}
