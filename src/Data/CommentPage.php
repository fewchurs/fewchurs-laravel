<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class CommentPage implements Arrayable
{
    /**
     * @param  Collection<int, Comment>  $comments
     */
    public function __construct(
        public Collection $comments,
        public ?string $nextCursor,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $deviceId = null): self
    {
        $comments = collect(self::rows($data['comments'] ?? null))
            ->map(fn (array $row) => Comment::fromArray($row, $deviceId));

        return new self(
            comments: $comments,
            nextCursor: isset($data['nextCursor']) ? (string) $data['nextCursor'] : null,
        );
    }

    public function hasMore(): bool
    {
        return $this->nextCursor !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'comments' => $this->comments->map->toArray()->all(),
            'nextCursor' => $this->nextCursor,
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
