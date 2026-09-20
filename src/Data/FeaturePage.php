<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class FeaturePage implements Arrayable
{
    /**
     * @param  Collection<int, Feature>  $features
     */
    public function __construct(
        public Collection $features,
        public ?string $nextCursor,
        public bool $isStale = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $deviceId = null, bool $isStale = false): self
    {
        $features = collect(self::rows($data['features'] ?? null))
            ->map(fn (array $row) => Feature::fromArray($row, $deviceId));

        return new self(
            features: $features,
            nextCursor: isset($data['nextCursor']) ? (string) $data['nextCursor'] : null,
            isStale: $isStale,
        );
    }

    public function isEmpty(): bool
    {
        return $this->features->isEmpty();
    }

    public function markStale(): self
    {
        return new self($this->features, $this->nextCursor, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'features' => $this->features->map->toArray()->all(),
            'nextCursor' => $this->nextCursor,
            'isStale' => $this->isStale,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function rows(mixed $value): array
    {
        $rows = [];

        foreach (is_array($value) ? $value : [] as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
