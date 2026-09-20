<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Events;

use Fewchurs\Laravel\Data\Feature;

final class FeatureSubmitted
{
    public function __construct(public readonly Feature $feature) {}
}
