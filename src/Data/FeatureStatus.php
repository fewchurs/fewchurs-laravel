<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

enum FeatureStatus: string
{
    case Open = 'open';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Declined = 'declined';
    case Unknown = 'unknown';

    public static function parse(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Unknown;
    }

    public function label(): string
    {
        return trans('fewchurs::messages.status.'.$this->value);
    }

    public function isPublic(): bool
    {
        return $this !== self::Declined;
    }
}
