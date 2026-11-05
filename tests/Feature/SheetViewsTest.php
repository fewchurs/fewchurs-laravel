<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;

it('opens the sheet frame on the list with a new request button', function () {
    fakeBoard();

    $html = $this->get('/feedback?embed=1')->assertOk()->getContent();

    expect($html)
        ->toContain('data-fewchurs-board data-view="browse"')
        ->toContain('New request')
        ->toContain('data-fewchurs-view="compose"')
        ->toContain(e(route('fewchurs.index', ['embed' => 1, 'tab' => 'top', 'compose' => 1])))
        ->toContain('Add dark mode');
});

it('keeps the form in the frame so the button can switch at once', function () {
    fakeBoard();

    $this->get('/feedback?embed=1')
        ->assertOk()
        ->assertSee('data-fewchurs-form', false)
        ->assertSee('data-fewchurs-view="browse"', false);
});

it('opens on the form from the plain link', function () {
    fakeBoard();

    $this->get('/feedback?embed=1&compose=1')->assertOk()->assertSee('data-fewchurs-board data-view="compose"', false);
});

it('stays on the form with the typed text after a validation error', function () {
    fakeBoard();

    $this->from('/feedback?embed=1')
        ->followingRedirects()
        ->post('/feedback/features', ['title' => 'ab', 'description' => 'Typed text', 'embed' => '1'])
        ->assertOk()
        ->assertSee('data-fewchurs-board data-view="compose"', false)
        ->assertSee('Typed text');
});

it('returns to the list after a request is sent', function () {
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features' => Http::response(fewchursFeature(['id' => 'new1']), 201),
        '*/features*' => Http::response(['features' => [], 'nextCursor' => null]),
    ]);

    $this->from('/feedback?embed=1&compose=1')
        ->post('/feedback/features', ['title' => 'Add dark mode', 'embed' => '1'])
        ->assertRedirect(route('fewchurs.index', ['embed' => '1']).'#fewchurs-feature-new1');
});

it('hides the button and the form when submissions are off', function () {
    config()->set('fewchurs.board.allow_submissions', false);
    fakeBoard();

    $this->get('/feedback?embed=1&compose=1')
        ->assertOk()
        ->assertSee('data-fewchurs-board data-view="browse"', false)
        ->assertDontSee('data-fewchurs-view="compose"', false)
        ->assertDontSee('data-fewchurs-form', false);
});

it('hides the button when the board fails', function () {
    Http::fake(['*' => Http::response(['error' => ['code' => 'internal', 'message' => 'boom']], 500)]);

    $this->get('/feedback?embed=1')->assertOk()->assertDontSee('data-fewchurs-view="compose"', false);
});

it('keeps the button above the branding', function () {
    fakeBoard();

    $this->get('/feedback?embed=1')->assertOk()->assertSee('class="fewchurs__fab"', false);
});

it('lowers the button when there is no branding under it', function () {
    fakeBoard(showBranding: false);

    $this->get('/feedback?embed=1')->assertOk()->assertSee('class="fewchurs__fab fewchurs__fab--low"', false);
});

it('leaves the normal board page alone', function () {
    fakeBoard();

    $this->get('/feedback')
        ->assertOk()
        ->assertSee('data-fewchurs-form', false)
        ->assertDontSee('data-fewchurs-board data-view', false)
        ->assertDontSee('data-fewchurs-view=', false);
});

it('shimmers the list and not the form while the sheet loads', function () {
    expect(Blade::render('<x-fewchurs::sheet />'))
        ->toContain('fewchurs-skeleton__card')
        ->not->toContain('width: 45%; height: 0.8rem;');
});
