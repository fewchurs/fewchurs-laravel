<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

final class ValidationFailedException extends FewchursException
{
    public function isCritical(): bool
    {
        return false;
    }

    protected function translationKey(): string
    {
        return 'validation_failed';
    }
}
