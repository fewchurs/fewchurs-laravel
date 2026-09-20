<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Events;

final class Unfollowed
{
    public function __construct(public readonly string $featureId) {}
}
