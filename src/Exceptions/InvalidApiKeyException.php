<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

final class InvalidApiKeyException extends FewchursException
{
    public function userMessage(): string
    {
        return $this->errorCode === 'wrong_app' && $this->getMessage() !== ''
            ? $this->getMessage()
            : parent::userMessage();
    }

    public function isCritical(): bool
    {
        return true;
    }

    protected function translationKey(): string
    {
        return 'invalid_key';
    }
}
