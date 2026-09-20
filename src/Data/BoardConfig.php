<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class BoardConfig implements Arrayable
{
    public function __construct(
        public bool $showBranding,
        public bool $hideVoteCounts = false,
        public ?string $appName = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            showBranding: (bool) ($data['showBranding'] ?? true),
            hideVoteCounts: (bool) ($data['hideVoteCounts'] ?? false),
            appName: isset($data['appName']) ? (string) $data['appName'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'showBranding' => $this->showBranding,
            'hideVoteCounts' => $this->hideVoteCounts,
            'appName' => $this->appName,
        ];
    }
}
