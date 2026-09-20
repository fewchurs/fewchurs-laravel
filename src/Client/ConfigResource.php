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

    public function get(): BoardConfig
    {
        try {
            $payload = $this->cache->remember(
                'config',
                (int) config('fewchurs.cache.config_ttl', 3600),
                fn () => $this->client->get('/config', withDevice: false),
            );
        } catch (FewchursException) {
            return new BoardConfig(showBranding: true);
        }

        return BoardConfig::fromArray($payload);
    }

    public function fresh(): BoardConfig
    {
        $this->cache->forget('config');

        return $this->get();
    }
}
