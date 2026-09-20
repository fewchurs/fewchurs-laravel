<?php

declare(strict_types=1);

use Fewchurs\Laravel\Livewire\FewchursBoard;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders the reactive board and votes without a page reload', function () {
    fakeBoard();
    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features/*/vote' => Http::response(['upvoteCount' => 5, 'hasVoted' => true]),
        '*/features*' => Http::response(['features' => [fewchursFeature()], 'nextCursor' => null]),
    ]);

    Livewire::test(FewchursBoard::class)
        ->assertSee('Add dark mode')
        ->call('toggleVote', 'feat123', false)
        ->assertHasNoErrors();
});

it('validates a reactive submission with the server limits', function () {
    fakeBoard();

    Livewire::test(FewchursBoard::class)
        ->set('title', 'ab')
        ->call('submit')
        ->assertHasErrors('title');
});
