<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Events;

use Fewchurs\Laravel\Data\VoteResult;

final class Unvoted
{
    public function __construct(
        public readonly string $featureId,
        public readonly VoteResult $result,
    ) {}
}
