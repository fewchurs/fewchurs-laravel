<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Identity;

use Illuminate\Http\Request;

interface DeviceIdResolver
{
    public function resolve(Request $request): string;
}
