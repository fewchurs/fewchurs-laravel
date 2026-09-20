<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

use Fewchurs\Laravel\Support\Redactor;
use RuntimeException;
use Throwable;

class FewchursException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'internal',
        public readonly int $status = 0,
        public readonly ?string $endpoint = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(Redactor::scrub($message), $status, $previous);
    }

    public static function fromEnvelope(string $code, string $message, int $status, string $endpoint): self
    {
        $class = match ($code) {
            'invalid_key' => InvalidApiKeyException::class,
            'missing_device_id' => MissingDeviceIdException::class,
            'validation_failed' => ValidationFailedException::class,
            'rate_limited' => RateLimitedException::class,
            'limit_reached' => LimitReachedException::class,
            'not_found' => NotFoundException::class,
            'unauthorized', 'forbidden' => InvalidApiKeyException::class,
            default => $status >= 500 ? ServerException::class : self::class,
        };

        return new $class($message, $code, $status, $endpoint);
    }

    /** Message safe to show a visitor; never the raw server text. */
    public function userMessage(): string
    {
        return trans('fewchurs::messages.errors.'.$this->translationKey());
    }

    public function isCritical(): bool
    {
        return false;
    }

    protected function translationKey(): string
    {
        return 'generic';
    }
}
