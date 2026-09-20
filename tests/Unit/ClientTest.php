<?php

declare(strict_types=1);

use Fewchurs\Laravel\Exceptions\InvalidApiKeyException;
use Fewchurs\Laravel\Exceptions\NotFoundException;
use Fewchurs\Laravel\Exceptions\RateLimitedException;
use Fewchurs\Laravel\Exceptions\ValidationFailedException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends the key, the device id and the sdk header on every call', function () {
    Http::fake(['*' => Http::response(['features' => [], 'nextCursor' => null])]);

    Fewchurs::features()->top();

    Http::assertSent(function (Request $request) {
        expect($request->header('Authorization')[0])->toBe('Bearer '.TestCase::API_KEY)
            ->and($request->header('X-Device-Id')[0])->toHaveLength(64)
            ->and($request->header('X-Fewchurs-Sdk')[0])->toStartWith('laravel/');

        return true;
    });
});

it('creates comments at /comments with the feature id in the body', function () {
    Http::fake(['*' => Http::response(['id' => 'c1', 'text' => '+1', 'authorName' => 'Sam'], 201)]);

    Fewchurs::comments()->create('feat123', '+1', 'Sam');

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/comments')
        && $request->method() === 'POST'
        && $request['featureId'] === 'feat123');
});

it('maps error envelopes to typed exceptions', function (string $code, int $status, string $class) {
    Http::fake(['*' => Http::response(['error' => ['code' => $code, 'message' => 'nope']], $status)]);

    expect(fn () => Fewchurs::features()->top())->toThrow($class);
})->with([
    ['invalid_key', 401, InvalidApiKeyException::class],
    ['validation_failed', 400, ValidationFailedException::class],
    ['rate_limited', 429, RateLimitedException::class],
    ['not_found', 404, NotFoundException::class],
]);

it('keeps paging on the cursor even when a comment page comes back empty', function () {
    Http::fake(['*' => Http::response(['comments' => [], 'nextCursor' => 'next123'])]);

    $page = Fewchurs::comments()->list('feat123');

    expect($page->comments)->toBeEmpty()->and($page->hasMore())->toBeTrue();
});

it('rejects ids that would escape the api path', function () {
    Http::fake();

    expect(fn () => Fewchurs::features()->vote('../../config'))->toThrow(NotFoundException::class);
    Http::assertNothingSent();
});

it('never exposes the key in an exception message', function () {
    Http::fake(['*' => Http::response(['error' => ['code' => 'internal', 'message' => 'key '.TestCase::API_KEY.' failed']], 500)]);

    try {
        Fewchurs::features()->top();
    } catch (Throwable $e) {
        expect($e->getMessage())->not->toContain(TestCase::API_KEY);
    }
});
