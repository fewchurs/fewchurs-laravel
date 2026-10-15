<?php

declare(strict_types=1);

namespace Fewchurs\Laravel;

use Fewchurs\Laravel\Client\Cache;
use Fewchurs\Laravel\Client\CommentsResource;
use Fewchurs\Laravel\Client\ConfigResource;
use Fewchurs\Laravel\Client\FeaturesResource;
use Fewchurs\Laravel\Client\FewchursClient;
use Fewchurs\Laravel\Data\BoardConfig;
use Fewchurs\Laravel\Identity\DeviceIdResolver;

final class FewchursApp
{
    private readonly FewchursClient $client;

    private readonly Cache $cache;

    private ?FeaturesResource $features = null;

    private ?CommentsResource $comments = null;

    private ?ConfigResource $config = null;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public readonly string $name,
        array $settings,
        DeviceIdResolver $identity,
    ) {
        $this->cache = new Cache($name);
        $this->client = new FewchursClient(
            apiKey: trim((string) ($settings['api_key'] ?? '')),
            baseUrl: (string) ($settings['base_url'] ?? 'https://www.fewchurs.shop/api/v1'),
            http: (array) ($settings['http'] ?? []),
            identity: $identity,
        );
    }

    public function features(): FeaturesResource
    {
        return $this->features ??= new FeaturesResource($this->client, $this->cache);
    }

    public function comments(): CommentsResource
    {
        return $this->comments ??= new CommentsResource($this->client, $this->cache);
    }

    public function config(): BoardConfig
    {
        return ($this->config ??= new ConfigResource($this->client, $this->cache))->get();
    }

    public function configResource(): ConfigResource
    {
        return $this->config ??= new ConfigResource($this->client, $this->cache);
    }

    public function client(): FewchursClient
    {
        return $this->client;
    }

    public function deviceId(): string
    {
        return $this->client->deviceId();
    }
}
