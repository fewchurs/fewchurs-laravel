<?php

declare(strict_types=1);

use Fewchurs\Laravel\Data\Comment;
use Fewchurs\Laravel\Data\Feature;
use Fewchurs\Laravel\Data\FeatureStatus;
use Fewchurs\Laravel\Support\Theme;

it('falls back to unknown for a status the server adds later', function () {
    expect(FeatureStatus::parse('shipped_to_mars'))->toBe(FeatureStatus::Unknown);
});

it('turns author device ids into isMine and drops them', function () {
    $feature = Feature::fromArray(fewchursFeature(['authorDeviceId' => 'device-a']), 'device-a');

    expect($feature->isMine)->toBeTrue()
        ->and($feature->toArray())->not->toHaveKey('authorDeviceId');

    $comment = Comment::fromArray(['id' => 'c', 'text' => 'hi', 'deviceId' => 'device-b'], 'device-a');

    expect($comment->isMine)->toBeFalse()
        ->and($comment->toArray())->not->toHaveKey('deviceId');
});

it('prefers the server isMine over the device id comparison', function () {
    expect(Feature::fromArray(fewchursFeature(['isMine' => true, 'authorDeviceId' => null]), 'device-a')->isMine)->toBeTrue()
        ->and(Feature::fromArray(fewchursFeature(['isMine' => false, 'authorDeviceId' => 'device-a']), 'device-a')->isMine)->toBeFalse()
        ->and(Comment::fromArray(['id' => 'c', 'text' => 'hi', 'isMine' => true], 'device-a')->isMine)->toBeTrue()
        ->and(Comment::fromArray(['id' => 'c', 'text' => 'hi', 'isMine' => false, 'deviceId' => 'device-a'], 'device-a')->isMine)->toBeFalse();
});

it('rejects theme values that are not plain colours or lengths', function () {
    expect(Theme::styles(['primary' => 'red; background: url(//evil)']))->toContain('--fewchurs-primary: #ED5F18')
        ->and(Theme::styles(['primary' => '#0F766E', 'radius' => '4px']))
        ->toContain('--fewchurs-primary: #0F766E')
        ->toContain('--fewchurs-radius: 4px');
});
