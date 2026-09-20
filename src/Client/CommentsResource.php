<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Client;

use Fewchurs\Laravel\Data\Comment;
use Fewchurs\Laravel\Data\CommentPage;
use Fewchurs\Laravel\Events\Commented;
use Fewchurs\Laravel\Support\Ids;
use Illuminate\Support\Facades\Event;

final class CommentsResource
{
    public function __construct(
        private readonly FewchursClient $client,
        private readonly Cache $cache,
    ) {}

    /**
     * Deleted comments are filtered after paging upstream, so a page can be short or
     * empty while nextCursor is set: page on the cursor, never on "the page was empty".
     */
    public function list(string $featureId, int $limit = 20, ?string $cursor = null): CommentPage
    {
        $limit = max(1, min(100, $limit));
        $cursor = $cursor !== null && Ids::isValid($cursor) ? $cursor : null;

        $payload = $this->client->get(
            '/features/'.Ids::assert($featureId).'/comments',
            ['limit' => $limit, 'cursor' => $cursor],
        );

        return CommentPage::fromArray($payload, $this->client->deviceId());
    }

    /**
     * Creation posts to /comments with featureId in the body — /features/{id}/comments is GET-only.
     */
    public function create(string $featureId, string $text, ?string $authorName = null): Comment
    {
        $comment = Comment::fromArray($this->client->post('/comments', array_filter([
            'featureId' => Ids::assert($featureId),
            'text' => $text,
            'authorName' => $authorName,
        ], fn ($value) => $value !== null)), $this->client->deviceId());

        $this->cache->bumpVersion();
        Event::dispatch(new Commented($featureId, $comment));

        return $comment;
    }
}
