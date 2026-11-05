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
    $this->get('/feedback')->assertOk()->assertSee('Invalid API key. Check the key in your Fewchurs dashboard.');

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

function refusedBoard(string $code, int $status, string $message): void
{
    Http::fake(['*' => Http::response(['error' => ['code' => $code, 'message' => $message]], $status)]);
}

it('shows developers the real cause while debug is on', function (string $code, int $status, string $message, string $shown) {
    config()->set('app.debug', true);
    refusedBoard($code, $status, $message);

    $this->get('/feedback')->assertOk()->assertSee($shown);
    $this->getJson('/feedback/features')->assertStatus($status)->assertJsonPath('error.message', $shown);
})->with([
    'server error' => ['internal', 500, 'boom', 'boom [internal, HTTP 500]'],
    'unknown code' => ['something_new', 418, 'A new error.', 'A new error. [something_new, HTTP 418]'],
]);

it('shows developers a lost connection while debug is on', function () {
    config()->set('app.debug', true);
    config()->set('fewchurs.http.retries', 0);
    Http::fake(fn () => throw new ConnectionException('timed out'));

    $this->get('/feedback')->assertOk()->assertSee('Could not reach the Fewchurs API.')->assertSee('[connection]');
});

it('shows developers a response that is not json while debug is on', function () {
    config()->set('app.debug', true);
    Http::fake(['*' => Http::response('<h1>Bad gateway</h1>', 502)]);

    $this->get('/feedback')->assertOk()->assertSee('Unexpected response from the Fewchurs API. [internal, HTTP 502]');
});

it('keeps the real cause away from visitors while debug is off', function (string $code, int $status, string $shown) {
    config()->set('app.debug', false);
    refusedBoard($code, $status, 'raw text about other.com');

    $this->get('/feedback')->assertOk()->assertSee($shown)->assertDontSee('other.com')->assertDontSee('raw text');
    $this->getJson('/feedback/features')->assertJsonPath('error.message', $shown);
})->with([
    'server error' => ['internal', 500, 'Something went wrong. Try again.'],
    'unknown code' => ['something_new', 418, 'Something went wrong. Try again.'],
]);

it('keeps precise messages for visitors even while debug is on', function () {
    config()->set('app.debug', true);
    refusedBoard('rate_limited', 429, 'raw limit text');

    $this->get('/feedback')->assertOk()->assertSee("You've reached today's limit. Try again tomorrow.")->assertDontSee('raw limit text');
});

it('never shows the key while debug is on', function () {
    config()->set('app.debug', true);
    refusedBoard('wrong_app', 403, 'key '.TestCase::API_KEY.' is bad');

    $this->get('/feedback')->assertOk()->assertSee('is bad')->assertDontSee(TestCase::API_KEY);
});

it('shows the real cause inside the sheet frame while debug is on', function () {
    config()->set('app.debug', true);
    refusedBoard('wrong_app', 403, 'This API key is not set up for other.com.');

    $this->get('/feedback?embed=1')
        ->assertOk()
        ->assertSee('data-fewchurs-board', false)
        ->assertSee('This API key is not set up for other.com.')
        ->assertDontSee('[wrong_app');
});

it('shows the same invalid key message with debug on or off', function (bool $debug) {
    config()->set('app.debug', $debug);
    refusedBoard('invalid_key', 401, 'raw key text');

    $this->get('/feedback')
        ->assertOk()
        ->assertSee('Invalid API key. Check the key in your Fewchurs dashboard.')
        ->assertDontSee('raw key text')
        ->assertDontSee('[invalid_key');
})->with([[true], [false]]);

it('shows the same wrong app message with debug on or off', function (bool $debug) {
    config()->set('app.debug', $debug);
    refusedBoard('wrong_app', 403, 'This API key is not set up for com.other.app. Add it to your app in the Fewchurs dashboard.');

    $this->get('/feedback')
        ->assertOk()
        ->assertSee('This API key is not set up for com.other.app. Add it to your app in the Fewchurs dashboard.')
        ->assertDontSee('[wrong_app');
})->with([[true], [false]]);

it('falls back to the invalid key message when wrong_app has no text', function () {
    refusedBoard('wrong_app', 403, '');

    $this->get('/feedback')->assertOk()->assertSee('Invalid API key. Check the key in your Fewchurs dashboard.');
});

it('marks only the sheet frame for the borderless layout', function () {
    fakeBoard();

    $this->get('/feedback?embed=1')->assertOk()->assertSee('fewchurs fewchurs--embed', false);
    $this->get('/feedback')->assertOk()->assertDontSee('class="fewchurs fewchurs--embed', false);
});

it('drops the line under the sheet title', function () {
    expect(Blade::render('<x-fewchurs::sheet />'))->not->toContain('border-bottom');
});
