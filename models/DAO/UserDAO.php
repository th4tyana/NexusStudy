<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../User.php';

class UserDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $name, string $email, string $password, string $userType, string $extraInfo = ''): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, user_type, extra_info) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash, $userType, $extraInfo]);
        return (int) $this->db->lastInsertId();
    }

    public function updateAccount(int $id, string $name, string $email, ?string $passwordHash, string $bio, string $avatarUrl, string $extraInfo): bool
    {
        if ($passwordHash !== null) {
            $stmt = $this->db->prepare(
                'UPDATE users SET name = ?, email = ?, password_hash = ?, bio = ?, avatar_url = ?, extra_info = ? WHERE id = ?'
            );
            return $stmt->execute([$name, $email, $passwordHash, $bio, $avatarUrl, $extraInfo, $id]);
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, email = ?, bio = ?, avatar_url = ?, extra_info = ? WHERE id = ?'
        );
        return $stmt->execute([$name, $email, $bio, $avatarUrl, $extraInfo, $id]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function emailExistsForOtherUser(string $email, int $currentUserId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $stmt->execute([$email, $currentUserId]);
        return (bool) $stmt->fetch();
    }

    public function updateProfile(int $id, string $name, string $bio, string $avatarUrl, string $extraInfo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, bio = ?, avatar_url = ?, extra_info = ? WHERE id = ?'
        );
        return $stmt->execute([$name, $bio, $avatarUrl, $extraInfo, $id]);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public function searchPeople(string $term): array
    {
        $cleanTerm = trim($term);
        if ($cleanTerm === '') {
            return [];
        }

        $searchTerm = '%' . strtolower($cleanTerm) . '%';
        $sql = '
            SELECT id, name, avatar_url, result_type
            FROM (
                SELECT id, name, avatar_url, "user" AS result_type
                FROM users
                WHERE user_type = "student"
                  AND (
                      LOWER(name) LIKE ?
                      OR LOWER(email) LIKE ?
                      OR LOWER(extra_info) LIKE ?
                  )
                UNION
                SELECT id, name, avatar_url, "institution" AS result_type
                FROM users
                WHERE user_type = "institution"
                  AND (
                      LOWER(name) LIKE ?
                      OR LOWER(email) LIKE ?
                      OR LOWER(extra_info) LIKE ?
                  )
            ) AS results
            ORDER BY name ASC
            LIMIT 20
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
