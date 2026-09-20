<?php

declare(strict_types=1);

use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\Identity\CookieDeviceId;
use Illuminate\Http\Request;

it('hashes the cookie so the raw value never leaves the app', function () {
    $resolver = new CookieDeviceId;
    $request = Request::create('/feedback');

    $first = $resolver->resolve($request);

    expect($first)->toMatch('/^[a-f0-9]{64}$/')
        ->and($resolver->resolve($request))->toBe($first);
});

it('keeps one identity per user across browsers', function () {
    config()->set('fewchurs.identity.mode', 'user');

    $user = new class
    {
        public function getAuthIdentifier(): int
        {
            return 7;
        }
    };

    $a = Request::create('/feedback');
    $a->setUserResolver(fn () => $user);
    $b = Request::create('/feedback');
    $b->setUserResolver(fn () => $user);

    $resolver = Fewchurs::identity();

    expect($resolver->resolve($a))->toBe($resolver->resolve($b));
});

it('refuses a custom resolver that returns an email', function () {
    Fewchurs::resolveDeviceIdUsing(fn () => 'someone@example.com');

    expect(fn () => Fewchurs::identity()->resolve(Request::create('/feedback')))
        ->toThrow(RuntimeException::class);
});
