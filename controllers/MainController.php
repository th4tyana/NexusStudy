<?php
require_once __DIR__ . '/../models/Models.php';

/**
 * EduConnect - MainController
 * Centraliza todas as rotas, validações e despacha as Views.
 * Arquitetura MVC com PHP puro.
 */
class MainController
{
    private User    $userModel;
    private Post    $postModel;
    private Like    $likeModel;
    private Comment $commentModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel    = new User();
        $this->postModel    = new Post();
        $this->likeModel    = new Like();
        $this->commentModel = new Comment();
    }

    // ----------------------------------------------------------------
    // ROTEADOR PRINCIPAL
    // ----------------------------------------------------------------
    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'feed';

        // Rotas públicas (não exigem login)
        if ($action === 'login')    { $this->showAuth();  return; }
        if ($action === 'register') { $this->showAuth();  return; }

        // Processar formulários de autenticação
        if ($action === 'do_login')    { $this->doLogin();    return; }
        if ($action === 'do_register') { $this->doRegister(); return; }

        // Rotas protegidas
        $this->requireLogin();

        match ($action) {
            'feed'           => $this->showFeed(),
            'post_create'    => $this->postCreate(),
            'post_edit'      => $this->showEditPost(),
            'post_update'    => $this->postUpdate(),
            'post_delete'    => $this->postDelete(),
            'like_toggle'    => $this->likeToggle(),
            'comment_create' => $this->commentCreate(),
            'edit_profile'   => $this->showEditProfile(),
            'profile_update' => $this->profileUpdate(),
            'logout'         => $this->doLogout(),
            default          => $this->showFeed(),
        };
    }

    // ----------------------------------------------------------------
    // AUTH
    // ----------------------------------------------------------------
    private function showAuth(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('feed');
        }
        require __DIR__ . '/../views/auth.php';
    }

    private function doLogin(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = '';

        if (empty($email) || empty($password)) {
            $error = 'Preencha todos os campos.';
        } else {
            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                $this->redirect('feed');
                return;
            }
            $error = 'E-mail ou senha incorretos.';
        }
        require __DIR__ . '/../views/auth.php';
    }

    private function doRegister(): void
    {
        $name      = trim($_POST['name']       ?? '');
        $email     = trim($_POST['email']      ?? '');
        $password  = trim($_POST['password']   ?? '');
        $userType  = $_POST['user_type']        ?? 'student';
        $extraInfo = trim($_POST['extra_info'] ?? '');
        $error     = '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Preencha todos os campos obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Endereço de e-mail inválido.';
        } elseif (strlen($password) < 6) {
            $error = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif ($this->userModel->emailExists($email)) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            $id = $this->userModel->create($name, $email, $password, $userType, $extraInfo);
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_type'] = $userType;
            $this->redirect('feed');
            return;
        }
        require __DIR__ . '/../views/auth.php';
    }

    private function doLogout(): void
    {
        session_destroy();
        $this->redirect('login');
    }

    // ----------------------------------------------------------------
    // FEED
    // ----------------------------------------------------------------
    private function showFeed(): void
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $posts         = $this->postModel->getAll($currentUserId);
        $currentUser   = $this->userModel->findById($currentUserId);

        // Injetar comentários em cada post
        foreach ($posts as &$post) {
            $post['comments'] = $this->commentModel->getByPost((int)$post['id']);
        }
        unset($post);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/feed_view.php';
    }

    // ----------------------------------------------------------------
    // POSTS CRUD
    // ----------------------------------------------------------------
    private function postCreate(): void
    {
        $content  = trim($_POST['content']   ?? '');
        $mediaUrl = trim($_POST['media_url'] ?? '');

        if (!empty($content)) {
            $this->postModel->create((int)$_SESSION['user_id'], $content, $mediaUrl);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação criada com sucesso!'];
        }
        $this->redirect('feed');
    }

    private function showEditPost(): void
    {
        $postId = (int)($_GET['id'] ?? 0);
        $post   = $this->postModel->findById($postId);

        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão para editar esta publicação.'];
            $this->redirect('feed');
            return;
        }
        require __DIR__ . '/../views/feed_view.php';
    }

    private function postUpdate(): void
    {
        $postId   = (int)($_POST['post_id']   ?? 0);
        $content  = trim($_POST['content']    ?? '');
        $mediaUrl = trim($_POST['media_url']  ?? '');
        $post     = $this->postModel->findById($postId);

        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão.'];
        } elseif (!empty($content)) {
            $this->postModel->update($postId, $content, $mediaUrl);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação atualizada.'];
        }
        $this->redirect('feed');
    }

    private function postDelete(): void
    {
        $postId = (int)($_GET['id'] ?? 0);
        $post   = $this->postModel->findById($postId);

        if ($post && $this->canModifyPost($post)) {
            $this->postModel->delete($postId);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação removida.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão para remover.'];
        }
        $this->redirect('feed');
    }

    // ----------------------------------------------------------------
    // LIKES
    // ----------------------------------------------------------------
    private function likeToggle(): void
    {
        $postId = (int)($_POST['post_id'] ?? 0);
        $result = $this->likeModel->toggle($postId, (int)$_SESSION['user_id']);
        $count  = $this->likeModel->countForPost($postId);

        // Resposta JSON para requisição AJAX
        header('Content-Type: application/json');
        echo json_encode(['action' => $result, 'count' => $count]);
        exit;
    }

    // ----------------------------------------------------------------
    // COMMENTS
    // ----------------------------------------------------------------
    private function commentCreate(): void
    {
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content']  ?? '');

        if (!empty($content)) {
            $result = $this->commentModel->create($postId, (int)$_SESSION['user_id'], $content);
            if (!$result['success']) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => $result['message']];
            }
        }
        $this->redirect('feed');
    }

    // ----------------------------------------------------------------
    // PERFIL
    // ----------------------------------------------------------------
    private function showEditProfile(): void
    {
        $currentUser = $this->userModel->findById((int)$_SESSION['user_id']);
        require __DIR__ . '/../views/edit_profile.php';
    }

    private function profileUpdate(): void
    {
        $id        = (int)$_SESSION['user_id'];
        $name      = trim($_POST['name']       ?? '');
        $bio       = trim($_POST['bio']        ?? '');
        $avatarUrl = trim($_POST['avatar_url'] ?? '');
        $extraInfo = trim($_POST['extra_info'] ?? '');

        if (empty($name)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'O nome não pode estar vazio.'];
        } else {
            $this->userModel->updateProfile($id, $name, $bio, $avatarUrl, $extraInfo);
            $_SESSION['user_name'] = $name;
            $_SESSION['flash']     = ['type' => 'success', 'msg' => 'Perfil atualizado com sucesso!'];
        }
        $this->redirect('edit_profile');
    }

    // ----------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------

    /** Verifica se o usuário logado pode modificar o post (autor ou instituição) */
    private function canModifyPost(array $post): bool
    {
        $uid  = (int) ($_SESSION['user_id']   ?? 0);
        $type = $_SESSION['user_type'] ?? '';
        return ($post['user_id'] == $uid) || ($type === 'institution');
    }

    private function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    private function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }

    private function redirect(string $action): void
    {
        header('Location: index.php?action=' . $action);
        exit;
    }
}
