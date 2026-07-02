<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Comment.php';

class CommentDAO
{
    private PDO $db;
    private array $forbidden = ['ódio','incompetente','burro','lixo','bosta','estúpido','merda'];

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getByPost(int $postId): array
    {
        $sql = '
            SELECT c.id, c.content, c.created_at, u.name AS author_name, u.avatar_url AS author_avatar
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function create(int $postId, int $userId, string $content): array
    {
        $lower = strtolower($content);
        foreach ($this->forbidden as $word) {
            if (str_contains($lower, $word)) {
                return ['success' => false, 'message' => 'Comentário bloqueado por conter linguagem inapropriada (RN03).'];
            }
        }
        $stmt = $this->db->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$postId, $userId, $content]);
        return ['success' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    public function countForPost(int $postId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int) $stmt->fetchColumn();
    }
}
