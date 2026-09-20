<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

final readonly class VoteResult
{
    public function __construct(
        public int $upvoteCount,
        public bool $hasVoted,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            upvoteCount: (int) ($data['upvoteCount'] ?? 0),
            hasVoted: (bool) ($data['hasVoted'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['upvoteCount' => $this->upvoteCount, 'hasVoted' => $this->hasVoted];
    }
}
