<?php

declare(strict_types=1);

use Fewchurs\Laravel\Livewire\FewchursBoard;
use Fewchurs\Laravel\Tests\TestCase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function failingBoard(string $code = 'internal', int $status = 500): void
{
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features*' => Http::response(['error' => ['code' => $code, 'message' => 'raw server text']], $status),
    ]);
}

it('shows a failure inside the board instead of breaking the host page', function () {
    failingBoard();

    $html = Blade::render('<h1>Host page</h1><x-fewchurs::board />');

    expect($html)
        ->toContain('Host page')
        ->toContain(e("Feature requests couldn't be loaded."))
        ->toContain('Something went wrong. Try again.')
        ->toContain('Try again')
        ->not->toContain('raw server text')
        ->not->toContain(TestCase::API_KEY);
});

it('explains a rejected key and an unreachable api in plain words', function () {
    failingBoard('invalid_key', 401);
    $this->get('/feedback')->assertOk()->assertSee('Feedback is temporarily unavailable.');

    Http::fake(fn () => throw new ConnectionException('timed out'));
    $this->get('/feedback')->assertOk()->assertSee("Couldn't reach the feedback service.");
});

it('keeps the board marker on a failed embed page so the sheet shows the board message', function () {
    failingBoard();

    $this->get('/feedback?embed=1')
        ->assertOk()
        ->assertSee('data-fewchurs-board', false)
        ->assertSee("Feature requests couldn't be loaded.")
        ->assertSee('embed=1', false);
});

it('gives the sheet a shimmer and its own error screen', function () {
    $html = Blade::render('<x-fewchurs::sheet />');

    expect($html)
        ->toContain('data-state="loading"')
        ->toContain('fewchurs-skeleton')
        ->toContain('Loading feature requests')
        ->toContain(e("Feedback couldn't be loaded right now."))
        ->toContain('data-fewchurs-sheet-retry')
        ->toContain('Open in a new page');
});

it('shows a failure inside the livewire board with a retry', function () {
    failingBoard('rate_limited', 429);

    Livewire::test(FewchursBoard::class)
        ->assertOk()
        ->assertSee("You've reached today's limit. Try again tomorrow.")
        ->assertSee('wire:click="$refresh"', false);
});

it('shows a shimmer while the livewire board switches tabs and when lazy', function () {
    fakeBoard();

    Livewire::test(FewchursBoard::class)
        ->assertSee('wire:loading.block', false)
        ->assertSee('fewchurs-skeleton', false);

    expect(view('fewchurs::livewire.placeholder')->render())->toContain('fewchurs-skeleton');
});
