<?php
declare(strict_types=1);

class Follow
{
    public function __construct(
        private int $id = 0,
        private int $followerId = 0,
        private int $followedId = 0,
        private ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (int) ($data['id_seguidor'] ?? 0),
            (int) ($data['id_seguido'] ?? 0),
            isset($data['created_at']) ? (string) $data['created_at'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_seguidor' => $this->followerId,
            'id_seguido' => $this->followedId,
            'created_at' => $this->createdAt,
        ];
    }
}
