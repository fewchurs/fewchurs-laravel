<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Testing;

use Fewchurs\Laravel\Data\Feature;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert;

/**
 * An in-memory board behind the real client, so host-app tests exercise the same
 * headers, validation and error mapping as production.
 */
final class FewchursFake
{
    /** @var array<string, array<string, mixed>> */
    private array $features = [];

    /** @var array<string, array<int, array<string, mixed>>> */
    private array $comments = [];

    /** @var array<int, array<string, mixed>> */
    private array $submitted = [];

    /** @var array<int, array<string, mixed>> */
    private array $posted = [];

    /** @var array<int, string> */
    private array $voted = [];

    /**
     * @param  array<int, mixed>  $features
     */
    public function __construct(array $features = [])
    {
        foreach ($features as $feature) {
            $row = $this->normalize($feature);
            $this->features[$row['id']] = $row;
        }
    }

    public function install(): self
    {
        $base = rtrim((string) config('fewchurs.base_url'), '/');

        Http::fake([$base.'/*' => fn (Request $request) => $this->handle($request)]);

        return $this;
    }

    public function assertSubmitted(?callable $callback = null): void
    {
        $matches = $callback === null
            ? $this->submitted
            : array_filter($this->submitted, fn (array $row) => $callback(Feature::fromArray($row)));

        Assert::assertNotEmpty($matches, 'No matching feature was submitted to Fewchurs.');
    }

    public function assertNothingSubmitted(): void
    {
        Assert::assertEmpty($this->submitted, 'A feature was submitted to Fewchurs.');
    }

    public function assertVoted(string $featureId): void
    {
        Assert::assertContains($featureId, $this->voted, "No vote was cast on [{$featureId}].");
    }

    public function assertCommented(?callable $callback = null): void
    {
        $matches = $callback === null
            ? $this->posted
            : array_filter($this->posted, fn (array $row) => $callback($row));

        Assert::assertNotEmpty($matches, 'No matching comment was posted to Fewchurs.');
    }

    /**
     * @return Response|PromiseInterface
     */
    private function handle(Request $request)
    {
        $path = '/'.trim((string) parse_url($request->url(), PHP_URL_PATH), '/');
        $path = Str::after($path, rtrim((string) parse_url((string) config('fewchurs.base_url'), PHP_URL_PATH), '/'));
        $method = strtoupper($request->method());
        $body = $request->data();

        if (! $request->hasHeader('Authorization')) {
            return Http::response(['error' => ['code' => 'invalid_key', 'message' => 'Missing key.']], 401);
        }

        return match (true) {
            $path === '/config' => Http::response(['showBranding' => true]),
            $path === '/features' && $method === 'GET' => Http::response([
                'features' => array_values($this->features),
                'nextCursor' => null,
            ]),
            $path === '/features' && $method === 'POST' => $this->submit($body),
            $path === '/comments' && $method === 'POST' => $this->comment($body),
            (bool) preg_match('#^/features/([^/]+)/vote$#', $path, $m) => $this->vote($m[1], $method === 'POST'),
            (bool) preg_match('#^/features/([^/]+)/follow$#', $path, $m) => Http::response(['following' => $method === 'POST']),
            (bool) preg_match('#^/features/([^/]+)/comments$#', $path, $m) => Http::response([
                'comments' => $this->comments[$m[1]] ?? [],
                'nextCursor' => null,
            ]),
            default => Http::response(['error' => ['code' => 'not_found', 'message' => 'Not found.']], 404),
        };
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function submit(array $body): mixed
    {
        $row = $this->normalize([
            'title' => $body['title'] ?? '',
            'description' => $body['description'] ?? '',
        ]);

        $this->features[$row['id']] = $row;
        $this->submitted[] = $row;

        return Http::response($row, 201);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function comment(array $body): mixed
    {
        $featureId = (string) ($body['featureId'] ?? '');

        if (! isset($this->features[$featureId])) {
            return Http::response(['error' => ['code' => 'not_found', 'message' => 'Feature not found.']], 404);
        }

        $comment = [
            'id' => (string) Str::uuid(),
            'text' => (string) ($body['text'] ?? ''),
            'authorName' => (string) ($body['authorName'] ?? ''),
            'isDeveloper' => false,
            'createdAt' => now()->toISOString(),
        ];

        $this->comments[$featureId][] = $comment;
        $this->posted[] = $comment + ['featureId' => $featureId];
        $this->features[$featureId]['commentCount']++;

        return Http::response($comment, 201);
    }

    private function vote(string $featureId, bool $add): mixed
    {
        if (! isset($this->features[$featureId])) {
            return Http::response(['error' => ['code' => 'not_found', 'message' => 'Feature not found.']], 404);
        }

        $feature = &$this->features[$featureId];

        if ($add && ! $feature['hasVoted']) {
            $feature['upvoteCount']++;
            $this->voted[] = $featureId;
        } elseif (! $add && $feature['hasVoted']) {
            $feature['upvoteCount'] = max(0, $feature['upvoteCount'] - 1);
        }

        $feature['hasVoted'] = $add;

        return Http::response(['upvoteCount' => $feature['upvoteCount'], 'hasVoted' => $add]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(mixed $feature): array
    {
        $data = match (true) {
            $feature instanceof Feature => $feature->toArray(),
            is_string($feature) => ['title' => $feature],
            is_array($feature) => $feature,
            default => [],
        };

        return [
            'id' => (string) ($data['id'] ?? Str::random(20)),
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'status' => (string) ($data['status'] ?? 'open'),
            'upvoteCount' => (int) ($data['upvoteCount'] ?? 0),
            'commentCount' => (int) ($data['commentCount'] ?? 0),
            'followerCount' => (int) ($data['followerCount'] ?? 0),
            'hasVoted' => (bool) ($data['hasVoted'] ?? false),
            'isFollowing' => (bool) ($data['isFollowing'] ?? false),
            'authorIsSubscriber' => (bool) ($data['authorIsSubscriber'] ?? false),
            'createdAt' => (string) ($data['createdAt'] ?? now()->toISOString()),
            'updatedAt' => (string) ($data['updatedAt'] ?? now()->toISOString()),
        ];
    }
}
