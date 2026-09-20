<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Tests;

use Fewchurs\Laravel\FewchursServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** Assembled at runtime so no key-shaped literal is ever committed. */
    public const API_KEY = 'fr_'.'live_'.'secret_key_value';

    protected function getPackageProviders($app): array
    {
        return array_filter([
            class_exists(LivewireServiceProvider::class) ? LivewireServiceProvider::class : null,
            FewchursServiceProvider::class,
        ]);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('fewchurs.api_key', self::API_KEY);
        $app['config']->set('fewchurs.cache.list_ttl', 0);
        $app['config']->set('fewchurs.cache.config_ttl', 0);
        $app['config']->set('fewchurs.route.honeypot', false);
    }
}
