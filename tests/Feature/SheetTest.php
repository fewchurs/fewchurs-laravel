<?php

declare(strict_types=1);

use Fewchurs\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;

it('renders a trigger that links to the board page and a frame that loads lazily', function () {
    $html = Blade::render('<x-fewchurs::sheet />');

    expect($html)
        ->toContain('href="'.route('fewchurs.index').'"')
        ->toContain('data-src="'.route('fewchurs.index', ['embed' => 1]).'"')
        ->toContain('<dialog')
        ->toContain('data-side="right"')
        ->toContain('Feedback')
        ->not->toContain(' src="');

    Http::assertNothingSent();
});

it('opens from the side it is given and falls back to the right', function () {
    expect(Blade::render('<x-fewchurs::sheet side="bottom" />'))->toContain('data-side="bottom"')
        ->and(Blade::render('<x-fewchurs::sheet side="left" />'))->toContain('data-side="left"')
        ->and(Blade::render('<x-fewchurs::sheet side="top;color:red" />'))->toContain('data-side="right"');
});

it('takes a custom label, or no trigger at all', function () {
    expect(Blade::render('<x-fewchurs::sheet>Ideas?</x-fewchurs::sheet>'))->toContain('Ideas?')
        ->and(Blade::render('<x-fewchurs::sheet label="Suggest" />'))->toContain('Suggest')
        ->and(Blade::render('<x-fewchurs::sheet :trigger="false" />'))
        ->not->toContain('fewchurs-sheet__trigger"')
        ->toContain('<dialog');
});

it('points the frame at another board and tab', function () {
    config()->set('fewchurs.apps', ['docs' => ['api_key' => TestCase::API_KEY]]);

    expect(Blade::render('<x-fewchurs::sheet app="docs" tab="new" />'))
        ->toContain('data-src="'.e(route('fewchurs.index', ['board' => 'docs', 'tab' => 'new', 'embed' => 1])).'"');
});

it('renders nothing without an api key', function () {
    config()->set('fewchurs.api_key', '');

    expect(trim(Blade::render('<x-fewchurs::sheet />')))->toBe('');
});

it('serves a bare embed page that only this site may frame', function () {
    fakeBoard();
    config()->set('fewchurs.board.layout', 'missing-host-layout');

    $response = $this->get('/feedback?embed=1')
        ->assertOk()
        ->assertSee('Add dark mode')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'");

    expect($response->getContent())->not->toContain(TestCase::API_KEY);

    $this->get('/feedback')->assertHeaderMissing('Content-Security-Policy');
});

it('keeps embed mode on every link and form inside the frame', function () {
    fakeBoard();

    $html = $this->get('/feedback?embed=1')->assertOk()->getContent();

    expect($html)
        ->toContain('<input type="hidden" name="embed" value="1">')
        ->toContain(e(route('fewchurs.index', ['embed' => 1, 'tab' => 'new'])));

    $plain = $this->get('/feedback')->getContent();

    expect($plain)->not->toContain('name="embed"')
        ->and($plain)->not->toContain('embed=1');
});

it('redirects a write from the frame back into the frame', function () {
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features/*/vote' => Http::response(['upvoteCount' => 5, 'hasVoted' => true]),
    ]);

    $this->post('/feedback/features/feat123/vote', ['embed' => '1'])
        ->assertRedirect(route('fewchurs.index', ['embed' => '1']).'#fewchurs-feature-feat123');

    $this->post('/feedback/features/feat123/vote', ['embed' => 'javascript:alert(1)'])
        ->assertRedirect(route('fewchurs.index').'#fewchurs-feature-feat123');
});
