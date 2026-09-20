<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Client;

use Fewchurs\Laravel\Data\Feature;
use Fewchurs\Laravel\Data\FeaturePage;
use Fewchurs\Laravel\Data\VoteResult;
use Fewchurs\Laravel\Events\FeatureSubmitted;
use Fewchurs\Laravel\Events\Followed;
use Fewchurs\Laravel\Events\Unfollowed;
use Fewchurs\Laravel\Events\Unvoted;
use Fewchurs\Laravel\Events\Voted;
use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Support\Ids;
use Illuminate\Support\Facades\Event;

final class FeaturesResource
{
    public function __construct(
        private readonly FewchursClient $client,
        private readonly Cache $cache,
    ) {}

    public function top(int $limit = 20, ?string $cursor = null): FeaturePage
    {
        return $this->list('top', $limit, $cursor);
    }

    public function latest(int $limit = 20, ?string $cursor = null): FeaturePage
    {
        return $this->list('new', $limit, $cursor);
    }

    public function list(string $sort = 'top', int $limit = 20, ?string $cursor = null): FeaturePage
    {
        $sort = $sort === 'new' ? 'new' : 'top';
        $limit = max(1, min(100, $limit));
        $cursor = $cursor !== null && Ids::isValid($cursor) ? $cursor : null;

        $deviceId = $this->client->deviceId();
        $key = sprintf(
            'list:%d:%s:%d:%s:%s',
            $this->cache->version(), $sort, $limit, $cursor ?? '-', substr(hash('sha256', $deviceId), 0, 16),
        );

        try {
            $payload = $this->cache->remember(
                $key,
                (int) config('fewchurs.cache.list_ttl', 15),
                fn () => $this->client->get('/features', ['sort' => $sort, 'limit' => $limit, 'cursor' => $cursor]),
            );
        } catch (FewchursException $e) {
            $stale = $this->cache->stale($key);

            if (is_array($stale)) {
                return FeaturePage::fromArray($stale, $deviceId, isStale: true);
            }

            throw $e;
        }

        $this->cache->putStale($key, $payload);

        return FeaturePage::fromArray($payload, $deviceId);
    }

    public function submit(
        string $title,
        ?string $description = null,
        ?string $email = null,
        bool $isSubscriber = false,
    ): Feature {
        $feature = Feature::fromArray($this->client->post('/features', array_filter([
            'title' => $title,
            'description' => $description,
            'email' => $email,
            'isSubscriber' => $isSubscriber,
        ], fn ($value) => $value !== null)), $this->client->deviceId());

        $this->cache->bumpVersion();
        Event::dispatch(new FeatureSubmitted($feature));

        return $feature;
    }

    public function vote(string $featureId): VoteResult
    {
        $result = VoteResult::fromArray(
            $this->client->post('/features/'.Ids::assert($featureId).'/vote', idempotent: true),
        );

        $this->cache->bumpVersion();
        Event::dispatch(new Voted($featureId, $result));

        return $result;
    }

    public function unvote(string $featureId): VoteResult
    {
        $result = VoteResult::fromArray(
            $this->client->delete('/features/'.Ids::assert($featureId).'/vote'),
        );

        $this->cache->bumpVersion();
        Event::dispatch(new Unvoted($featureId, $result));

        return $result;
    }

    public function follow(string $featureId, string $email): bool
    {
        $this->client->post('/features/'.Ids::assert($featureId).'/follow', ['email' => $email], idempotent: true);

        $this->cache->bumpVersion();
        Event::dispatch(new Followed($featureId));

        return true;
    }

    public function unfollow(string $featureId): bool
    {
        $this->client->delete('/features/'.Ids::assert($featureId).'/follow');

        $this->cache->bumpVersion();
        Event::dispatch(new Unfollowed($featureId));

        return false;
    }
}
