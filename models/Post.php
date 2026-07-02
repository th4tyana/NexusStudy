<?php
declare(strict_types=1);

class Post
{
    public function __construct(
        private int $id = 0,
        private int $userId = 0,
        private string $content = '',
        private string $mediaUrl = '',
        private ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (int) ($data['user_id'] ?? 0),
            (string) ($data['content'] ?? ''),
            (string) ($data['media_url'] ?? ''),
            isset($data['created_at']) ? (string) $data['created_at'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'content' => $this->content,
            'media_url' => $this->mediaUrl,
            'created_at' => $this->createdAt,
        ];
    }
}
