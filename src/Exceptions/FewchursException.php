<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Exceptions;

use Fewchurs\Laravel\Support\Redactor;
use RuntimeException;
use Throwable;

class FewchursException extends RuntimeException
{
    private const VAGUE_MESSAGES = ['generic', 'connection', 'server'];

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
            'invalid_key', 'wrong_app' => InvalidApiKeyException::class,
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

    /** Message safe to show a visitor; the real cause only while APP_DEBUG is on. */
    public function userMessage(): string
    {
        if (config('app.debug') && in_array($this->translationKey(), self::VAGUE_MESSAGES, true)) {
            return $this->developerMessage();
        }

        return trans('fewchurs::messages.errors.'.$this->translationKey());
    }

    private function developerMessage(): string
    {
        $message = $this->getMessage() ?: 'The Fewchurs API returned an error.';

        return $this->status > 0
            ? "{$message} [{$this->errorCode}, HTTP {$this->status}]"
            : "{$message} [{$this->errorCode}]";
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
