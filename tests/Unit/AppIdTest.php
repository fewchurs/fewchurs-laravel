<?php

declare(strict_types=1);

use Fewchurs\Laravel\Exceptions\InvalidApiKeyException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function wrongAppResponse(): array
{
    return ['error' => [
        'code' => 'wrong_app',
        'message' => 'This API key is not set up for shop.test.',
    ]];
}

function freshConfigFile(): array
{
    return require __DIR__.'/../../config/fewchurs.php';
}

it('defaults the app id to the host of the app url', function () {
    config()->set('app.url', 'https://shop.example.com:8443/store');

    expect(freshConfigFile()['app_id'])->toBe('shop.example.com');
});

it('has no app id when the app url is missing', function (?string $url) {
    config()->set('app.url', $url);

    expect(freshConfigFile()['app_id'])->toBeNull();
})->with([[null], ['']]);

it('lets the env value win over the app url', function () {
    config()->set('app.url', 'https://shop.example.com');
    $_ENV['FEWCHURS_APP_ID'] = 'custom.example.com';

    try {
        expect(freshConfigFile()['app_id'])->toBe('custom.example.com');
    } finally {
        unset($_ENV['FEWCHURS_APP_ID']);
    }
});

it('sends the app id on every endpoint', function (Closure $call) {
    config()->set('fewchurs.app_id', 'shop.example.com');
    Http::fake(['*' => Http::response(fewchursFeature(['upvoteCount' => 1]))]);

    $call();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->header('X-Fewchurs-App-Id') === ['shop.example.com']);
})->with([
    'config' => [fn () => Fewchurs::app()->configResource()->fresh()],
    'top' => [fn () => Fewchurs::features()->top()],
    'latest' => [fn () => Fewchurs::features()->latest()],
    'submit' => [fn () => Fewchurs::features()->submit('Add dark mode')],
    'vote' => [fn () => Fewchurs::features()->vote('feat123')],
    'unvote' => [fn () => Fewchurs::features()->unvote('feat123')],
    'follow' => [fn () => Fewchurs::features()->follow('feat123', 'a@b.com')],
    'unfollow' => [fn () => Fewchurs::features()->unfollow('feat123')],
    'list comments' => [fn () => Fewchurs::comments()->list('feat123')],
    'create comment' => [fn () => Fewchurs::comments()->create('feat123', '+1')],
]);

it('sends the app id from the proxy routes and not the browser origin', function () {
    config()->set('fewchurs.app_id', 'shop.example.com');
    Http::fake(['*' => Http::response(['upvoteCount' => 5, 'hasVoted' => true])]);

    $this->postJson('/feedback/features/feat123/vote', [], ['Origin' => 'https://evil.test'])->assertOk();

    Http::assertSent(fn (Request $request) => $request->header('X-Fewchurs-App-Id') === ['shop.example.com']
        && ! $request->hasHeader('Origin'));
});

it('leaves the header off when there is no app id', function () {
    config()->set('fewchurs.app_id', '');
    Http::fake(['*' => Http::response(['features' => [], 'nextCursor' => null])]);

    Fewchurs::features()->top();

    Http::assertSent(fn (Request $request) => ! $request->hasHeader('X-Fewchurs-App-Id'));
});

it('lets a named board use its own app id', function () {
    config()->set('fewchurs.app_id', 'shop.example.com');
    config()->set('fewchurs.apps', [
        'docs' => ['api_key' => 'docs-key', 'app_id' => 'docs.example.com'],
        'help' => ['api_key' => 'help-key'],
    ]);
    Http::fake(['*' => Http::response(['features' => [], 'nextCursor' => null])]);

    Fewchurs::app('docs')->features()->top();
    Fewchurs::app('help')->features()->top();

    Http::assertSent(fn (Request $request) => $request->header('Authorization') === ['Bearer docs-key']
        && $request->header('X-Fewchurs-App-Id') === ['docs.example.com']);
    Http::assertSent(fn (Request $request) => $request->header('Authorization') === ['Bearer help-key']
        && $request->header('X-Fewchurs-App-Id') === ['shop.example.com']);
});

it('maps wrong_app to the invalid key exception and keeps the message', function () {
    Http::fake(['*' => Http::response(wrongAppResponse(), 403)]);

    try {
        Fewchurs::features()->top();
        $this->fail('Expected an exception.');
    } catch (InvalidApiKeyException $e) {
        expect($e->getMessage())->toBe('This API key is not set up for shop.test.')
            ->and($e->errorCode)->toBe('wrong_app')
            ->and($e->status)->toBe(403);
    }
});

it('never retries wrong_app', function () {
    config()->set('fewchurs.http.retries', 3);
    Http::fake(['*' => Http::response(wrongAppResponse(), 403)]);

    expect(fn () => Fewchurs::features()->top())->toThrow(InvalidApiKeyException::class);

    Http::assertSentCount(1);
});

it('logs the wrong_app message once per failed call', function () {
    Log::shouldReceive('channel')->andReturnSelf();
    Log::shouldReceive('log')
        ->once()
        ->withArgs(fn (string $level, string $message, array $context) => $level === 'critical'
            && $context['code'] === 'wrong_app'
            && $context['message'] === 'This API key is not set up for shop.test.');
    Http::fake(['*' => Http::response(wrongAppResponse(), 403)]);

    expect(fn () => Fewchurs::features()->top())->toThrow(InvalidApiKeyException::class);
});

it('keeps other server messages out of the log', function () {
    Log::shouldReceive('channel')->andReturnSelf();
    Log::shouldReceive('log')
        ->once()
        ->withArgs(fn (string $level, string $message, array $context) => ! array_key_exists('message', $context));
    Http::fake(['*' => Http::response(['error' => ['code' => 'invalid_key', 'message' => 'nope']], 401)]);

    expect(fn () => Fewchurs::features()->top())->toThrow(InvalidApiKeyException::class);
});

it('shows visitors the plain message, not the app id', function () {
    Http::fake(['*' => Http::response(wrongAppResponse(), 403)]);

    $this->get('/feedback')
        ->assertOk()
        ->assertSee(trans('fewchurs::messages.errors.invalid_key'))
        ->assertDontSee('shop.test');

    $this->postJson('/feedback/features/feat123/vote')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'wrong_app')
        ->assertJsonPath('error.message', trans('fewchurs::messages.errors.invalid_key'));
});

it('tells the developer which id to add in fewchurs:check', function () {
    Http::fake(['*' => Http::response(wrongAppResponse(), 403)]);

    $this->artisan('fewchurs:check')
        ->expectsOutputToContain('This API key is not set up for shop.test.')
        ->doesntExpectOutputToContain('Board reachable')
        ->assertFailed();
});
