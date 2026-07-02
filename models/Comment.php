<?php
declare(strict_types=1);

class Comment
{
    public function __construct(
        private int $id = 0,
        private int $postId = 0,
        private int $userId = 0,
        private string $content = '',
        private ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (int) ($data['post_id'] ?? 0),
            (int) ($data['user_id'] ?? 0),
            (string) ($data['content'] ?? ''),
            isset($data['created_at']) ? (string) $data['created_at'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->postId,
            'user_id' => $this->userId,
            'content' => $this->content,
            'created_at' => $this->createdAt,
        ];
    }
}
