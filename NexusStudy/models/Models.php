<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

// ============================================================
// MODEL: User
// ============================================================
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Busca usuário por e-mail para autenticação */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /** Busca usuário por ID */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Cria novo usuário — retorna o ID inserido */
    public function create(string $name, string $email, string $password, string $userType, string $extraInfo = ''): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, user_type, extra_info) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash, $userType, $extraInfo]);
        return (int) $this->db->lastInsertId();
    }

    /** Atualiza as informações da conta do usuário */
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

    /** Exclui o usuário do banco de dados */
    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** Verifica se o e-mail pertence a outro usuário */
    public function emailExistsForOtherUser(string $email, int $currentUserId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $stmt->execute([$email, $currentUserId]);
        return (bool) $stmt->fetch();
    }

    /** Atualiza perfil do usuário */
    public function updateProfile(int $id, string $name, string $bio, string $avatarUrl, string $extraInfo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET name = ?, bio = ?, avatar_url = ?, extra_info = ? WHERE id = ?'
        );
        return $stmt->execute([$name, $bio, $avatarUrl, $extraInfo, $id]);
    }

    /** Verifica se o e-mail já está cadastrado */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    /** Busca global por usuários e instituições usando correspondência parcial */
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

// ============================================================
// MODEL: Follow
// ============================================================
class Follow
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
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

// ============================================================
// MODEL: Post
// ============================================================
class Post
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Retorna todos os posts com dados do autor e contagem de likes */
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

    /** Retorna posts de um usuário específico */
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

    /** Busca post por ID */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Cria novo post */
    public function create(int $userId, string $content, string $mediaUrl = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, media_url) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $mediaUrl ?: null]);
        return (int) $this->db->lastInsertId();
    }

    /** Atualiza post (apenas autor ou instituição podem chamar — a verificação fica no Controller) */
    public function update(int $id, string $content, string $mediaUrl = ''): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = ?, media_url = ? WHERE id = ?'
        );
        return $stmt->execute([$content, $mediaUrl ?: null, $id]);
    }

    /** Exclui post */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

// ============================================================
// MODEL: Like
// ============================================================
class Like
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Alterna like (toggle): se já existir remove, senão insere */
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

    /** Contagem de likes de um post */
    public function countForPost(int $postId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int) $stmt->fetchColumn();
    }
}

// ============================================================
// MODEL: Comment
// ============================================================
class Comment
{
    private PDO $db;

    // Palavras bloqueadas por moderação automática (RN03)
    private array $forbidden = ['ódio','incompetente','burro','lixo','bosta','estúpido','merda'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Retorna comentários de um post */
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

    /** Cria comentário com filtro anti-ódio */
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

    /** Contagem de comentários por post */
    public function countForPost(int $postId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int) $stmt->fetchColumn();
    }
}
