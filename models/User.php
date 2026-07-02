<?php
declare(strict_types=1);

class User
{
    public function __construct(
        private int $id = 0,
        private string $name = '',
        private string $email = '',
        private string $passwordHash = '',
        private string $userType = 'student',
        private string $bio = '',
        private string $avatarUrl = '',
        private string $extraInfo = '',
        private ?string $createdAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (string) ($data['name'] ?? ''),
            (string) ($data['email'] ?? ''),
            (string) ($data['password_hash'] ?? ''),
            (string) ($data['user_type'] ?? 'student'),
            (string) ($data['bio'] ?? ''),
            (string) ($data['avatar_url'] ?? ''),
            (string) ($data['extra_info'] ?? ''),
            isset($data['created_at']) ? (string) $data['created_at'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'user_type' => $this->userType,
            'bio' => $this->bio,
            'avatar_url' => $this->avatarUrl,
            'extra_info' => $this->extraInfo,
            'created_at' => $this->createdAt,
        ];
    }
}
