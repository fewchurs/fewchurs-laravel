<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Events;

use Fewchurs\Laravel\Exceptions\FewchursException;

final class RequestFailed
{
    public function __construct(public readonly FewchursException $exception) {}
}
