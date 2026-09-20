<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

final class InvalidApiKeyException extends FewchursException
{
    public function isCritical(): bool
    {
        return true;
    }

    protected function translationKey(): string
    {
        return 'invalid_key';
    }
}
