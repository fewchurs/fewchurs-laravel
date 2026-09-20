<?php

declare(strict_types=1);

use Fewchurs\Laravel\Support\Honeypot;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('fewchurs.route.honeypot', true);
    config()->set('fewchurs.honeypot.min_seconds', 0);

    Http::fake([
        '*/config' => Http::response(['showBranding' => true]),
        '*/features' => Http::response(fewchursFeature(), 201),
        '*/features*' => Http::response(['features' => [], 'nextCursor' => null]),
    ]);
});

it('drops a submission that fills the decoy field', function () {
    $this->post('/feedback/features', [
        'title' => 'Add dark mode',
        Honeypot::field() => 'http://spam.test',
        Honeypot::timestampField() => Honeypot::token(),
    ])->assertSessionHasErrors('title');

    Http::assertNotSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/features'));
});

it('drops a submission with a forged timestamp', function () {
    $this->post('/feedback/features', [
        'title' => 'Add dark mode',
        Honeypot::timestampField() => 'not-a-real-token',
    ])->assertSessionHasErrors('title');
});

it('accepts a submission carrying a real token', function () {
    $this->post('/feedback/features', [
        'title' => 'Add dark mode',
        Honeypot::field() => '',
        Honeypot::timestampField() => Honeypot::token(),
    ])->assertSessionHasNoErrors();
});
