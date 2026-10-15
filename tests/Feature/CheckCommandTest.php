<?php

declare(strict_types=1);

use Fewchurs\Laravel\Facades\Fewchurs;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

it('defaults to the live api', function () {
    $config = require __DIR__.'/../../config/fewchurs.php';

    expect($config['base_url'])->toBe(env('FEWCHURS_BASE_URL', 'https://www.fewchurs.shop/api/v1'))
        ->and(file_get_contents(__DIR__.'/../../config/fewchurs.php'))
        ->toContain("env('FEWCHURS_BASE_URL', 'https://www.fewchurs.shop/api/v1')");
});

it('reports a reachable board and the plan from the branding flag', function (bool $showBranding, string $line) {
    Http::fake(['*/config' => Http::response(['showBranding' => $showBranding])]);

    $this->artisan('fewchurs:check')
        ->expectsOutputToContain('Board reachable')
        ->expectsOutputToContain($line)
        ->assertSuccessful();
})->with([
    [true, 'shown (Free plan)'],
    [false, 'hidden (Starter or Pro plan)'],
]);

it('fails when the api key is rejected', function () {
    Http::fake(['*' => Http::response(['error' => ['code' => 'invalid_key', 'message' => 'nope']], 401)]);

    $this->artisan('fewchurs:check')
        ->expectsOutputToContain('API key rejected')
        ->doesntExpectOutputToContain('Board reachable')
        ->assertFailed();
});

it('fails when the api is unreachable', function () {
    config()->set('fewchurs.http.retries', 0);
    Http::fake(['*' => fn () => throw new ConnectionException('Could not resolve host')]);

    $this->artisan('fewchurs:check')
        ->expectsOutputToContain('Board unreachable')
        ->doesntExpectOutputToContain('Board reachable')
        ->assertFailed();
});

it('still renders the board with the badge when config fails', function () {
    Http::fake(['*' => Http::response(['error' => ['code' => 'internal', 'message' => 'boom']], 500)]);

    expect(Fewchurs::config()->showBranding)->toBeTrue();
});
