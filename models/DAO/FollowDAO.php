<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Follow.php';

class FollowDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function follow(int $followerId, int $followedId): bool
    {
        if ($followerId === $followedId) {
            return false;
        }

        $stmt = $this->db->prepare('INSERT INTO seguidores (id_seguidor, id_seguido) VALUES (?, ?)');
        return $stmt->execute([$followerId, $followedId]);
    }

    public function unfollow(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM seguidores WHERE id_seguidor = ? AND id_seguido = ?');
        return $stmt->execute([$followerId, $followedId]);
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM seguidores WHERE id_seguidor = ? AND id_seguido = ? LIMIT 1');
        $stmt->execute([$followerId, $followedId]);
        return (bool) $stmt->fetch();
    }

    public function countFollowers(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM seguidores WHERE id_seguido = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countFollowing(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM seguidores WHERE id_seguidor = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getFollowers(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.name, u.avatar_url, u.user_type FROM seguidores s JOIN users u ON u.id = s.id_seguidor WHERE s.id_seguido = ? ORDER BY u.name ASC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getFollowing(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.name, u.avatar_url, u.user_type FROM seguidores s JOIN users u ON u.id = s.id_seguido WHERE s.id_seguidor = ? ORDER BY u.name ASC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
