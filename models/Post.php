<?php
declare(strict_types=1);

class Post
{
    public function __construct(
        private int $id = 0,
        private int $userId = 0,
        private string $content = '',
        private string $mediaUrl = '',
        private ?string $createdAt = null,
        private bool $isStudyGuide = false,
        private string $courseName = '',
        private string $entryType = '',
        private string $weights = '',
        private string $pdfUrl = ''
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (int) ($data['user_id'] ?? 0),
            (string) ($data['content'] ?? ''),
            (string) ($data['media_url'] ?? ''),
            isset($data['created_at']) ? (string) $data['created_at'] : null,
            (bool) ($data['is_study_guide'] ?? false),
            (string) ($data['course_name'] ?? ''),
            (string) ($data['entry_type'] ?? ''),
            (string) ($data['weights'] ?? ''),
            (string) ($data['pdf_url'] ?? '')
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
            'is_study_guide' => $this->isStudyGuide,
            'course_name' => $this->courseName,
            'entry_type' => $this->entryType,
            'weights' => $this->weights,
            'pdf_url' => $this->pdfUrl,
        ];
    }
}