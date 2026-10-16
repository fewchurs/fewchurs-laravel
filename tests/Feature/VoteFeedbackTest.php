<?php

declare(strict_types=1);

use Fewchurs\Laravel\Livewire\FewchursBoard;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('gives the vote button what it needs to flip at once', function () {
    fakeBoard();

    $html = $this->get('/feedback')->assertOk()->getContent();

    expect($html)
        ->toContain('data-vote-url="'.route('fewchurs.vote', ['feature' => 'feat123']).'"')
        ->toContain('data-unvote-url="'.route('fewchurs.unvote', ['feature' => 'feat123']).'"')
        ->toContain('data-label-vote="Upvote this request"')
        ->toContain('data-label-remove="Remove your vote"');
});

it('hands the saved vote back to the livewire button without re-rendering the board', function () {
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features/*/vote' => Http::response(['upvoteCount' => 5, 'hasVoted' => true]),
        '*/features*' => Http::response(['features' => [fewchursFeature()], 'nextCursor' => null]),
    ]);

    Livewire::test(FewchursBoard::class)
        ->assertSee('x-on:click="toggle()"', false)
        ->call('toggleVote', 'feat123', false)
        ->assertReturned(['hasVoted' => true, 'upvoteCount' => 5])
        ->assertHasNoErrors();
});

it('reports a failed livewire vote so the button can undo itself', function () {
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features/*/vote' => Http::response(['error' => ['code' => 'rate_limited', 'message' => 'slow down']], 429),
        '*/features*' => Http::response(['features' => [fewchursFeature()], 'nextCursor' => null]),
    ]);

    Livewire::test(FewchursBoard::class)
        ->call('toggleVote', 'feat123', false)
        ->assertReturned(null)
        ->assertHasErrors('board')
        ->assertSee("You've reached today's limit. Try again tomorrow.");
});
