<?php

declare(strict_types=1);

use Fewchurs\Laravel\Livewire\FewchursBoard;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

const LOCAL_NOTE = 'Before you go live add your domain in the';

it('shows the note on the board and in the sheet frame for a local id', function (string $id) {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', $id);
    fakeBoard();

    $this->get('/feedback')->assertOk()->assertSee(LOCAL_NOTE);
    $this->get('/feedback?embed=1')->assertOk()->assertSee(LOCAL_NOTE);
})->with(['localhost', '127.0.0.1', '[::1]', 'LOCALHOST']);

it('shows the note on the livewire board', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', 'localhost');
    fakeBoard();

    Livewire::test(FewchursBoard::class)->assertSee(LOCAL_NOTE);
});

it('hides the note on a real domain', function (string $id) {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', $id);
    fakeBoard();

    $this->get('/feedback')->assertOk()->assertDontSee(LOCAL_NOTE);
})->with(['testlint.com', 'localhost.testlint.com', '']);

it('hides the note from visitors while debug is off, even on a local id', function () {
    config()->set('app.debug', false);
    config()->set('fewchurs.app_id', 'localhost');
    fakeBoard();

    $this->get('/feedback')->assertOk()->assertDontSee(LOCAL_NOTE);
    Livewire::test(FewchursBoard::class)->assertDontSee(LOCAL_NOTE);
});

it('hides the note while the board shows an error', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', 'localhost');
    Http::fake(['*' => Http::response(['error' => ['code' => 'invalid_key', 'message' => 'nope']], 401)]);

    $this->get('/feedback')->assertOk()->assertDontSee(LOCAL_NOTE);
});

it('uses the id of the board being shown', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', 'localhost');
    config()->set('fewchurs.apps.docs', ['api_key' => 'docs-key', 'app_id' => 'docs.testlint.com']);
    fakeBoard();

    $this->get('/feedback?board=docs')->assertOk()->assertDontSee(LOCAL_NOTE);
});

it('links to the dashboard and uses no commas', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', 'localhost');
    fakeBoard();

    $html = $this->get('/feedback?embed=1')->assertOk()->getContent();

    expect($html)->toContain('href="https://www.fewchurs.com/dashboard"')
        ->toContain('>Fewchurs dashboard</a> under Web')
        ->and(trans('fewchurs::messages.local_note'))->not->toContain(',');
});

it('still shows the link when a translation drops the placeholder', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.app_id', 'localhost');
    trans('fewchurs::messages.back');
    app('translator')->addLines(['messages.local_note' => 'Add your domain.'], 'en', 'fewchurs');
    fakeBoard();

    $this->get('/feedback')->assertOk()->assertSee('Add your domain.')->assertSee('Fewchurs dashboard');
});
