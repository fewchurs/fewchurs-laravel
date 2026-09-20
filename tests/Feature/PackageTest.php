<?php

declare(strict_types=1);

use Fewchurs\Laravel\Facades\Fewchurs;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

it('registers the board route only while a prefix is configured', function () {
    expect(Route::has('fewchurs.index'))->toBeTrue();
});

it('exposes a fake board for host app tests', function () {
    $fake = Fewchurs::fake([['id' => 'feat1', 'title' => 'Add dark mode']]);

    Fewchurs::features()->vote('feat1');
    Fewchurs::features()->submit(title: 'Ship an API');

    $fake->assertVoted('feat1');
    $fake->assertSubmitted(fn ($feature) => $feature->title === 'Ship an API');
});

it('serves stale data instead of an error page when the api is down', function () {
    Http::fake(['*/features*' => Http::sequence()
        ->push(['features' => [fewchursFeature()], 'nextCursor' => null])
        ->push(['error' => ['code' => 'internal', 'message' => 'boom']], 500),
    ]);

    Fewchurs::features()->top();
    $page = Fewchurs::features()->top();

    expect($page->isStale)->toBeTrue()->and($page->features)->toHaveCount(1);
});

it('returns 404 instead of leaking a stack trace when no key is configured', function () {
    config()->set('fewchurs.api_key', null);

    $this->get('/feedback')->assertNotFound();
    $this->post('/feedback/features', ['title' => 'Add dark mode'])->assertNotFound();
});
