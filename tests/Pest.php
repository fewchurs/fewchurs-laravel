<?php

declare(strict_types=1);

use Fewchurs\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class)->in('Feature', 'Unit');

function fewchursFeature(array $overrides = []): array
{
    return array_merge([
        'id' => 'feat123',
        'title' => 'Add dark mode',
        'description' => 'Would love a dark theme.',
        'status' => 'open',
        'upvoteCount' => 4,
        'commentCount' => 0,
        'followerCount' => 0,
        'hasVoted' => false,
        'isFollowing' => false,
        'authorDeviceId' => 'someone-else',
        'authorIsSubscriber' => false,
        'createdAt' => '2026-01-01T00:00:00.000Z',
        'updatedAt' => '2026-01-01T00:00:00.000Z',
    ], $overrides);
}

/**
 * Http::fake() merges stubs and the first match wins, so each test builds its
 * board in one call rather than layering a second fake on top.
 */
function fakeBoard(?array $features = null, bool $showBranding = true): void
{
    Http::fake([
        '*/config' => Http::response(['showBranding' => $showBranding]),
        '*/features*' => Http::response([
            'features' => $features ?? [fewchursFeature()],
            'nextCursor' => null,
        ]),
    ]);
}
