<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Client;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache as CacheFacade;

/**
 * List entries are keyed per device because hasVoted/isFollowing are per visitor —
 * a shared entry would hand one visitor's vote state to everyone. A stale copy is
 * kept far longer so an unreachable API degrades into "showing recent data".
 */
final class Cache
{
    public function __construct(private readonly string $app) {}

    public function store(): Repository
    {
        return CacheFacade::store(config('fewchurs.cache.store'));
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $key = $this->prefix($key);

        if ($ttl <= 0) {
            return $callback();
        }

        $cached = $this->store()->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $this->store()->put($key, $value, $ttl);

        return $value;
    }

    public function putStale(string $key, mixed $value): void
    {
        $this->store()->put(
            $this->prefix('stale:'.$key),
            $value,
            (int) config('fewchurs.cache.stale_ttl', 86400),
        );
    }

    public function stale(string $key): mixed
    {
        return $this->store()->get($this->prefix('stale:'.$key));
    }

    public function version(): int
    {
        return (int) $this->store()->get("fewchurs:{$this->app}:v", 1);
    }

    public function bumpVersion(): void
    {
        $this->store()->forever("fewchurs:{$this->app}:v", $this->version() + 1);
    }

    public function forget(string $key): void
    {
        $this->store()->forget($this->prefix($key));
    }

    private function prefix(string $key): string
    {
        return "fewchurs:{$this->app}:{$key}";
    }
}
