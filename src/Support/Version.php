<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Support;

use Illuminate\Foundation\Application;

final class Version
{
    public const SDK = '1.4.0';

    public static function userAgent(): string
    {
        $laravel = class_exists(Application::class) ? Application::VERSION : 'unknown';

        return sprintf('laravel/%s (php %s; laravel %s)', self::SDK, PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, $laravel);
    }
}
