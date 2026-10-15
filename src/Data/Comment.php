<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class Comment implements Arrayable
{
    public function __construct(
        public string $id,
        public string $text,
        public string $authorName,
        public bool $isDeveloper,
        public bool $isMine,
        public ?string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $deviceId = null): self
    {
        $authorDeviceId = isset($data['deviceId']) ? (string) $data['deviceId'] : null;

        return new self(
            id: (string) ($data['id'] ?? ''),
            text: (string) ($data['text'] ?? ''),
            authorName: (string) ($data['authorName'] ?? ''),
            isDeveloper: (bool) ($data['isDeveloper'] ?? false),
            // Prefer the server's answer; raw device ids may stop being sent.
            isMine: isset($data['isMine'])
                ? (bool) $data['isMine']
                : $deviceId !== null && $authorDeviceId !== null && hash_equals($authorDeviceId, $deviceId),
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'authorName' => $this->authorName,
            'isDeveloper' => $this->isDeveloper,
            'isMine' => $this->isMine,
            'createdAt' => $this->createdAt,
        ];
    }
}
