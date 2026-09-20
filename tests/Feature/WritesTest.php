<?php

declare(strict_types=1);

use Fewchurs\Laravel\Events\FeatureSubmitted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features/*/vote' => Http::response(['upvoteCount' => 5, 'hasVoted' => true]),
        '*/features' => Http::response(fewchursFeature(['id' => 'new1']), 201),
        '*/features*' => Http::response(['features' => [], 'nextCursor' => null]),
    ]);
});

it('submits a request and redirects back to the board, not to the referer', function () {
    Event::fake([FeatureSubmitted::class]);

    $this->from('https://evil.test/phish')
        ->post('/feedback/features', ['title' => 'Add dark mode', 'description' => 'Please'])
        ->assertRedirect(route('fewchurs.index').'#fewchurs-feature-new1');

    Event::assertDispatched(FeatureSubmitted::class);
});

it('validates with the same limits as the server', function () {
    $this->post('/feedback/features', ['title' => 'ab'])->assertSessionHasErrors('title');
    $this->post('/feedback/features', ['title' => str_repeat('a', 101)])->assertSessionHasErrors('title');
    $this->post('/feedback/features', [
        'title' => 'Valid title',
        'description' => str_repeat('a', 1001),
    ])->assertSessionHasErrors('description');
});

it('guards every write route with the web group and a per-visitor throttle', function () {
    $writes = collect(Route::getRoutes())
        ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'fewchurs.')
            && ! in_array('GET', $route->methods(), true));

    expect($writes)->not->toBeEmpty();

    $writes->each(function ($route) {
        expect($route->gatherMiddleware())->toContain('web')->toContain('throttle:fewchurs');
    });
});

it('returns json to xhr votes', function () {
    $this->postJson('/feedback/features/feat123/vote')
        ->assertOk()
        ->assertJson(['upvoteCount' => 5, 'hasVoted' => true]);
});

it('refuses a board name that is not configured', function () {
    $this->post('/feedback/features', ['title' => 'Add dark mode', 'board' => 'secret'])
        ->assertNotFound();
});

it('closes submissions when the config says so', function () {
    config()->set('fewchurs.board.allow_submissions', false);

    $this->post('/feedback/features', ['title' => 'Add dark mode'])
        ->assertSessionHasErrors('title');
});
