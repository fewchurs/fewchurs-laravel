<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class Feature implements Arrayable
{
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public FeatureStatus $status,
        public int $upvoteCount,
        public int $commentCount,
        public int $followerCount,
        public bool $hasVoted,
        public bool $isFollowing,
        public bool $isMine,
        public bool $authorIsSubscriber,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?string $deviceId = null): self
    {
        $authorDeviceId = isset($data['authorDeviceId']) ? (string) $data['authorDeviceId'] : null;

        return new self(
            id: (string) ($data['id'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            status: FeatureStatus::parse(isset($data['status']) ? (string) $data['status'] : null),
            upvoteCount: (int) ($data['upvoteCount'] ?? 0),
            commentCount: (int) ($data['commentCount'] ?? 0),
            followerCount: (int) ($data['followerCount'] ?? 0),
            hasVoted: (bool) ($data['hasVoted'] ?? false),
            isFollowing: (bool) ($data['isFollowing'] ?? false),
            // Prefer the server's answer; raw device ids may stop being sent.
            isMine: isset($data['isMine'])
                ? (bool) $data['isMine']
                : $deviceId !== null && $authorDeviceId !== null && hash_equals($authorDeviceId, $deviceId),
            authorIsSubscriber: (bool) ($data['authorIsSubscriber'] ?? false),
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            updatedAt: isset($data['updatedAt']) ? (string) $data['updatedAt'] : null,
        );
    }

    public function withVote(bool $hasVoted, int $upvoteCount): self
    {
        return new self(
            $this->id, $this->title, $this->description, $this->status,
            $upvoteCount, $this->commentCount, $this->followerCount,
            $hasVoted, $this->isFollowing, $this->isMine, $this->authorIsSubscriber,
            $this->createdAt, $this->updatedAt,
        );
    }

    public function withFollowing(bool $isFollowing): self
    {
        return new self(
            $this->id, $this->title, $this->description, $this->status,
            $this->upvoteCount, $this->commentCount, $this->followerCount,
            $this->hasVoted, $isFollowing, $this->isMine, $this->authorIsSubscriber,
            $this->createdAt, $this->updatedAt,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'upvoteCount' => $this->upvoteCount,
            'commentCount' => $this->commentCount,
            'followerCount' => $this->followerCount,
            'hasVoted' => $this->hasVoted,
            'isFollowing' => $this->isFollowing,
            'isMine' => $this->isMine,
            'authorIsSubscriber' => $this->authorIsSubscriber,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
