<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Facades;

use Fewchurs\Laravel\Client\CommentsResource;
use Fewchurs\Laravel\Client\FeaturesResource;
use Fewchurs\Laravel\Data\BoardConfig;
use Fewchurs\Laravel\FewchursApp;
use Fewchurs\Laravel\Testing\FewchursFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static FewchursApp app(?string $name = null)
 * @method static FeaturesResource features()
 * @method static CommentsResource comments()
 * @method static BoardConfig config()
 * @method static string deviceId()
 * @method static void resolveDeviceIdUsing(\Closure $callback)
 * @method static bool configured(?string $name = null)
 * @method static FewchursFake fake(array<int, mixed> $features = [])
 *
 * @see \Fewchurs\Laravel\Fewchurs
 */
final class Fewchurs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fewchurs\Laravel\Fewchurs::class;
    }
}
