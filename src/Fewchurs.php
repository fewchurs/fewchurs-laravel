<?php

declare(strict_types=1);

namespace Fewchurs\Laravel;

use Closure;
use Fewchurs\Laravel\Client\CommentsResource;
use Fewchurs\Laravel\Client\FeaturesResource;
use Fewchurs\Laravel\Data\BoardConfig;
use Fewchurs\Laravel\Identity\CallbackDeviceId;
use Fewchurs\Laravel\Identity\CookieDeviceId;
use Fewchurs\Laravel\Identity\DeviceIdResolver;
use Fewchurs\Laravel\Identity\UserDeviceId;
use Fewchurs\Laravel\Testing\FewchursFake;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class Fewchurs
{
    /** @var array<string, FewchursApp> */
    private array $apps = [];

    private ?DeviceIdResolver $identity = null;

    public const DEFAULT_APP = 'default';

    public function app(?string $name = null): FewchursApp
    {
        $name ??= self::DEFAULT_APP;

        return $this->apps[$name] ??= new FewchursApp($name, $this->settings($name), $this->identity());
    }

    public function features(): FeaturesResource
    {
        return $this->app()->features();
    }

    public function comments(): CommentsResource
    {
        return $this->app()->comments();
    }

    public function config(): BoardConfig
    {
        return $this->app()->config();
    }

    public function deviceId(): string
    {
        return $this->app()->deviceId();
    }

    /**
     * @param  Closure(Request): string  $callback
     */
    public function resolveDeviceIdUsing(Closure $callback): void
    {
        $this->identity = new CallbackDeviceId($callback);
        $this->apps = [];
    }

    public function identity(): DeviceIdResolver
    {
        return $this->identity ??= match ((string) config('fewchurs.identity.mode', 'hybrid')) {
            'cookie' => new CookieDeviceId,
            default => new UserDeviceId(new CookieDeviceId),
        };
    }

    public function configured(?string $name = null): bool
    {
        return trim((string) ($this->settings($name ?? self::DEFAULT_APP)['api_key'] ?? '')) !== '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     */
    public function fake(array $features = []): FewchursFake
    {
        $this->apps = [];

        return (new FewchursFake($features))->install();
    }

    /**
     * @return array<string, mixed>
     */
    private function settings(string $name): array
    {
        $base = [
            'api_key' => config('fewchurs.api_key'),
            'base_url' => config('fewchurs.base_url'),
            'http' => config('fewchurs.http', []),
        ];

        if ($name === self::DEFAULT_APP) {
            return $base;
        }

        $app = config('fewchurs.apps.'.$name);

        if (! is_array($app)) {
            throw new InvalidArgumentException("Fewchurs board [{$name}] is not configured.");
        }

        return array_merge($base, $app);
    }
}
