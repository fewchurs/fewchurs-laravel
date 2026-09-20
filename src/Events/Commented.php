<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Events;

use Fewchurs\Laravel\Data\Comment;

final class Commented
{
    public function __construct(
        public readonly string $featureId,
        public readonly Comment $comment,
    ) {}
}
