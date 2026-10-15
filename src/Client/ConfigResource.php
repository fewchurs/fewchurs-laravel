<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Client;

use Fewchurs\Laravel\Data\BoardConfig;
use Fewchurs\Laravel\Exceptions\FewchursException;

final class ConfigResource
{
    public function __construct(
        private readonly FewchursClient $client,
        private readonly Cache $cache,
    ) {}

    /**
     * Lenient: the board should still render (with the badge) when /config fails.
     */
    public function get(): BoardConfig
    {
        try {
            return $this->fetch();
        } catch (FewchursException) {
            return new BoardConfig(showBranding: true);
        }
    }

    /**
     * Strict: throws instead of falling back, so fewchurs:check and fewchurs:warm
     * can report a dead URL or a rejected key.
     *
     * @throws FewchursException
     */
    public function fetch(): BoardConfig
    {
        $payload = $this->cache->remember(
            'config',
            (int) config('fewchurs.cache.config_ttl', 300),
            fn () => $this->client->get('/config', withDevice: false),
        );

        return BoardConfig::fromArray($payload);
    }

    /**
     * Strict, bypassing the cache.
     *
     * @throws FewchursException
     */
    public function fresh(): BoardConfig
    {
        $this->cache->forget('config');

        return $this->fetch();
    }
}
