<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

final class MissingDeviceIdException extends FewchursException
{
    public function isCritical(): bool
    {
        return true;
    }

    protected function translationKey(): string
    {
        return 'generic';
    }
}
