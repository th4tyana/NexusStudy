<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Post.php';

class PostDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function getAll(int $currentUserId = 0): array
    {
        $sql = '
            SELECT
                p.id,
                p.content,
                p.media_url,
                p.created_at,
                u.id        AS author_id,
                u.name      AS author_name,
                u.avatar_url AS author_avatar,
                u.user_type  AS author_type,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = :uid) AS liked_by_me
            FROM posts p
            JOIN users u ON u.id = p.user_id
            ORDER BY p.created_at DESC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $currentUserId]);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, int $viewerId = 0): array
    {
        $sql = '
            SELECT
                p.id,
                p.content,
                p.media_url,
                p.created_at,
                u.id        AS author_id,
                u.name      AS author_name,
                u.avatar_url AS author_avatar,
                u.user_type  AS author_type,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id AND l.user_id = :uid) AS liked_by_me
            FROM posts p
            JOIN users u ON u.id = p.user_id
            WHERE p.user_id = :user_id
            ORDER BY p.created_at DESC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $viewerId, ':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(int $userId, string $content, string $mediaUrl = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, media_url) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $mediaUrl ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $content, string $mediaUrl = ''): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = ?, media_url = ? WHERE id = ?'
        );
        return $stmt->execute([$content, $mediaUrl ?: null, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
