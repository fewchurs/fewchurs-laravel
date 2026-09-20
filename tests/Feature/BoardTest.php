<?php

declare(strict_types=1);

use Fewchurs\Laravel\Tests\TestCase;

it('renders the board without javascript', function () {
    fakeBoard();

    $this->get('/feedback')
        ->assertOk()
        ->assertSee('Add dark mode')
        ->assertSee('Powered by Fewchurs');
});

it('never leaks the api key into the page', function () {
    fakeBoard();

    $response = $this->get('/feedback')->assertOk();

    expect($response->getContent())->not->toContain(TestCase::API_KEY)
        ->and($response->getContent())->not->toContain('Bearer');
});

it('escapes user content', function () {
    fakeBoard([fewchursFeature(['title' => '<script>alert(1)</script>'])]);

    $response = $this->get('/feedback')->assertOk();

    expect($response->getContent())
        ->not->toContain('<script>alert(1)</script>')
        ->toContain('&lt;script&gt;');
});

it('serves json without device ids', function () {
    fakeBoard();

    $json = $this->getJson('/feedback/features')->assertOk()->json();

    expect($json['features'][0])->not->toHaveKey('authorDeviceId')
        ->and($json['features'][0])->toHaveKey('isMine');
});

it('ignores a tab that is not configured', function () {
    fakeBoard();

    $this->get('/feedback?tab=../admin')->assertOk();
});
