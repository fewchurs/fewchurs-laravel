<?php

declare(strict_types=1);

namespace Fewchurs\Laravel;

use Fewchurs\Laravel\Console\CheckCommand;
use Fewchurs\Laravel\Console\InstallCommand;
use Fewchurs\Laravel\Console\WarmCommand;
use Fewchurs\Laravel\Http\Middleware\EnsureBoardEnabled;
use Fewchurs\Laravel\Livewire\FewchursBoard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class FewchursServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fewchurs.php', 'fewchurs');

        $this->app->singleton(Fewchurs::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'fewchurs');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fewchurs');

        Blade::componentNamespace('Fewchurs\\Laravel\\View\\Components', 'fewchurs');
        Blade::directive('fewchursHoneypot', fn () => "<?php echo view('fewchurs::partials.honeypot')->render(); ?>");

        $this->registerRateLimiter();
        $this->registerRoutes();
        $this->registerLivewire();

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->commands([InstallCommand::class, CheckCommand::class, WarmCommand::class]);
        }
    }

    private function registerRoutes(): void
    {
        $prefix = config('fewchurs.route.prefix');

        if ($prefix === null || $prefix === '') {
            return;
        }

        Route::group([
            'prefix' => $prefix,
            'as' => (string) config('fewchurs.route.name', 'fewchurs.'),
            'middleware' => array_merge(
                (array) config('fewchurs.route.middleware', ['web']),
                [EnsureBoardEnabled::class],
            ),
        ], fn () => $this->loadRoutesFrom(__DIR__.'/../routes/fewchurs.php'));
    }

    /** Writes are limited per visitor, not per IP: one server IP fronts every visitor. */
    private function registerRateLimiter(): void
    {
        [$max, $minutes] = array_pad(
            array_map('intval', explode(',', (string) config('fewchurs.route.throttle', '30,1'))),
            2,
            1,
        );

        RateLimiter::for('fewchurs', fn (Request $request) => Limit::perMinutes(
            max(1, $minutes),
            max(1, $max),
        )->by($this->app->make(Fewchurs::class)->identity()->resolve($request)));
    }

    private function registerLivewire(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('fewchurs-board', FewchursBoard::class);
        }
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/fewchurs.php' => config_path('fewchurs.php'),
        ], 'fewchurs-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/fewchurs'),
        ], 'fewchurs-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/fewchurs'),
        ], 'fewchurs-lang');
    }
}
