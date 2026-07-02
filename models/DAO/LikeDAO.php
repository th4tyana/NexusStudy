<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Like.php';

class LikeDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function toggle(int $postId, int $userId): string
    {
        $check = $this->db->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
        $check->execute([$postId, $userId]);
        if ($check->fetch()) {
            $this->db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?')
                     ->execute([$postId, $userId]);
            return 'removed';
        }
        $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)')
                 ->execute([$postId, $userId]);
        return 'added';
    }

    public function countForPost(int $postId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int) $stmt->fetchColumn();
    }
}
