<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

final class ConnectionException extends FewchursException
{
    public function isCritical(): bool
    {
        return false;
    }

    protected function translationKey(): string
    {
        return 'connection';
    }
}
