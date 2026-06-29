<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Models.php';

/**
 * EduConnect - MainController
 * Centraliza todas as rotas, validações e despacha as Views.
 * Arquitetura MVC com PHP puro.
 */
class MainController
{
    private const UPLOAD_DIR = __DIR__ . '/../uploads';
    private const UPLOAD_URL = 'uploads';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5MB
    private const GOOGLE_CLIENT_ID = '';
    private const GOOGLE_CLIENT_SECRET = '';
    private const GOOGLE_OAUTH_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_OAUTH_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    private const GOOGLE_OAUTH_SCOPE = 'openid email profile';
    private const GOOGLE_REDIRECT_PATH = 'index.php?action=google_callback';

    private User    $userModel;
    private Post    $postModel;
    private Like    $likeModel;
    private Comment $commentModel;
    private Follow  $followModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel    = new User();
        $this->postModel    = new Post();
        $this->likeModel    = new Like();
        $this->commentModel = new Comment();
        $this->followModel  = new Follow();
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
        if ($action === 'do_login')       { $this->doLogin();       return; }
        if ($action === 'do_register')    { $this->doRegister();    return; }
        if ($action === 'google_login')   { $this->doGoogleLogin(); return; }
        if ($action === 'google_callback') { $this->doGoogleCallback(); return; }

        // Rotas públicas adicionais
        if ($action === 'institution_profile') { $this->showInstitutionProfile(); return; }
        if ($action === 'user_profile') { $this->showUserProfile(); return; }

        // Rotas protegidas
        $this->requireLogin();

        match ($action) {
            'feed'                => $this->showFeed(),
            'search_global'       => $this->searchGlobal(),
            'search'              => $this->searchGlobal(),
            'search_autocomplete' => $this->searchAutocomplete(),
            'toggle_follow'       => $this->toggleFollow(),
            'follow_list'         => $this->followList(),
            'post_create'         => $this->postCreate(),
            'post_edit'           => $this->showEditPost(),
            'post_update'         => $this->postUpdate(),
            'post_delete'         => $this->postDelete(),
            'like_toggle'         => $this->likeToggle(),
            'comment_create'      => $this->commentCreate(),
            'profile'             => $this->showProfile(),
            'edit_profile'        => $this->showEditProfile(),
            'profile_update'      => $this->profileUpdate(),
            'profile_delete'      => $this->profileDelete(),
            'logout'              => $this->doLogout(),
            default               => $this->showFeed(),
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

    private function doGoogleLogin(): void
    {
        $clientId = $this->getGoogleClientId();
        if ($clientId === '') {
            $error = 'Configuração do Google OAuth não encontrada. Defina GOOGLE_CLIENT_ID.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth2_state'] = $state;

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $this->getGoogleRedirectUri(),
            'response_type' => 'code',
            'scope'         => self::GOOGLE_OAUTH_SCOPE,
            'access_type'   => 'offline',
            'prompt'        => 'select_account',
            'state'         => $state,
        ];

        header('Location: ' . self::GOOGLE_OAUTH_AUTH_URL . '?' . http_build_query($params));
        exit;
    }

    private function doGoogleCallback(): void
    {
        $error = '';

        if (!isset($_GET['state'], $_SESSION['oauth2_state']) || $_GET['state'] !== $_SESSION['oauth2_state']) {
            $error = 'Falha na validação de segurança do Google OAuth.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        unset($_SESSION['oauth2_state']);

        if (!empty($_GET['error'])) {
            $error = 'A autenticação com o Google foi cancelada ou falhou.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        if (empty($_GET['code'])) {
            $error = 'Resposta inválida do Google. Código não encontrado.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $tokenResponse = $this->fetchGoogleToken((string) $_GET['code']);
        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            $error = 'Não foi possível obter o token de acesso do Google.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $userInfo = $this->fetchGoogleUserInfo((string) $tokenResponse['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            $error = 'Não foi possível recuperar os dados do usuário Google.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $email = (string) $userInfo['email'];
        $name  = trim((string) ($userInfo['name'] ?? $userInfo['email'] ?? 'Usuário'));

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $password = bin2hex(random_bytes(8));
            $userId = $this->userModel->create($name, $email, $password, 'student', '');
            $user = $this->userModel->findById($userId);
        }

        if (!$user) {
            $error = 'Falha ao criar ou carregar usuário após autenticação do Google.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        $this->redirect('feed');
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
        $posts         = $this->hydratePostsWithComments($this->postModel->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->getCurrentUserData($currentUserId);
        $searchQuery   = '';
        $searchResults = [];

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/feed_view.php';
    }

    private function searchGlobal(): void
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $searchQuery   = trim($_GET['q'] ?? $_POST['q'] ?? '');
        $searchResults = [];
        $posts         = $this->hydratePostsWithComments($this->postModel->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->getCurrentUserData($currentUserId);

        if ($searchQuery !== '') {
            $searchResults = $this->userModel->searchPeople($searchQuery);
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/feed_view.php';
    }

    private function searchAutocomplete(): void
    {
        $term = trim($_GET['q'] ?? $_POST['q'] ?? '');
        if ($term === '') {
            echo json_encode([]);
            return;
        }

        $results = $this->userModel->searchPeople($term);
        echo json_encode($results);
    }

    private function toggleFollow(): void
    {
        $viewerId = (int)($_SESSION['user_id'] ?? 0);
        $targetId = (int)($_POST['target_id'] ?? 0);

        if ($viewerId <= 0 || $targetId <= 0 || $viewerId === $targetId) {
            echo json_encode(['success' => false]);
            return;
        }

        $isFollowing = $this->followModel->isFollowing($viewerId, $targetId);
        if ($isFollowing) {
            $this->followModel->unfollow($viewerId, $targetId);
            echo json_encode(['success' => true, 'following' => false]);
        } else {
            $this->followModel->follow($viewerId, $targetId);
            echo json_encode(['success' => true, 'following' => true]);
        }
    }

    private function followList(): void
    {
        $viewerId = (int)($_SESSION['user_id'] ?? 0);
        $userId   = (int)($_GET['id'] ?? 0);
        $type     = $_GET['type'] ?? 'followers';

        if ($userId <= 0) {
            echo '';
            return;
        }

        $items = $type === 'following'
            ? $this->followModel->getFollowing($userId)
            : $this->followModel->getFollowers($userId);

        if (empty($items)) {
            echo '<p class="text-sm text-slate-500">Nenhuma pessoa nesta lista.</p>';
            return;
        }

        echo '<ul class="space-y-2">';
        foreach ($items as $item) {
            $label = $item['user_type'] === 'institution' ? 'institution_profile' : 'user_profile';
            echo '<li><a href="index.php?action=' . $label . '&id=' . (int)$item['id'] . '" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-2 hover:bg-slate-50">';
            if (!empty($item['avatar_url'])) {
                echo '<img src="' . htmlspecialchars($item['avatar_url']) . '" class="w-9 h-9 rounded-full object-cover" alt="Avatar">';
            } else {
                echo '<div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">' . strtoupper(substr($item['name'] ?? 'U', 0, 1)) . '</div>';
            }
            echo '<span class="text-sm font-semibold text-slate-800">' . htmlspecialchars($item['name']) . '</span></a></li>';
        }
        echo '</ul>';
    }

    // ----------------------------------------------------------------
    // POSTS CRUD
    // ----------------------------------------------------------------
    private function postCreate(): void
    {
        $content    = trim($_POST['content'] ?? '');
        $redirectTo = in_array($_POST['redirect_to'] ?? '', ['feed', 'profile'], true) ? $_POST['redirect_to'] : 'feed';
        $mediaUrl   = $this->handleUpload('media_file');

        if (!empty($content)) {
            $this->postModel->create((int)$_SESSION['user_id'], $content, $mediaUrl);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação criada com sucesso!'];
        }
        $this->redirect($redirectTo);
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
        $postId     = (int)($_POST['post_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');
        $existing   = trim($_POST['existing_media_url'] ?? '');
        $post       = $this->postModel->findById($postId);
        $uploadUrl  = $this->handleUpload('media_file');
        $mediaUrl   = $uploadUrl !== '' ? $uploadUrl : $existing;

        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão.'];
        } elseif (!empty($content)) {
            $this->postModel->update($postId, $content, $mediaUrl);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação atualizada.'];
        }
        $this->redirect('feed');
    }

    private function handleUpload(string $fieldName): string
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return '';
        }

        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Falha no upload da imagem. Tente novamente.'];
            return '';
        }

        if ($file['size'] > self::MAX_UPLOAD_SIZE) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'A imagem deve ter no máximo 5MB.'];
            return '';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = match ($extension) {
            'jpg', 'jpeg' => 'jpg',
            'png'         => 'png',
            'gif'         => 'gif',
            'webp'        => 'webp',
            default       => '',
        };

        if ($ext === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Somente imagens JPEG, PNG, GIF ou WEBP são permitidas.'];
            return '';
        }

        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0755, true) && !is_dir(self::UPLOAD_DIR)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Não foi possível criar o diretório de upload.'];
            return '';
        }

        $fileName = sprintf('%s.%s', bin2hex(random_bytes(16)), $ext);
        $destination = self::UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Erro ao salvar a imagem enviada.'];
            return '';
        }

        return self::UPLOAD_URL . '/' . $fileName;
    }

    private function postDelete(): void
    {
        $postId = (int)($_GET['id'] ?? 0);
        $post   = $this->postModel->findById($postId);

        if ($post && $this->canModifyPost($post)) {
            $this->postModel->delete($postId);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação removida.' ];
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
    private function showProfile(): void
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUser   = $this->getCurrentUserData($currentUserId);
        $posts         = $this->hydratePostsWithComments($this->postModel->getByUser($currentUserId), $currentUserId);
        $followModel   = $this->followModel;

        require __DIR__ . '/../views/profile.php';
    }

    private function showEditProfile(): void
    {
        $currentUser = $this->getCurrentUserData((int)($_SESSION['user_id'] ?? 0));
        require __DIR__ . '/../views/edit_profile.php';
    }

    private function showUserProfile(): void
    {
        $viewerId    = (int)($_SESSION['user_id'] ?? 0);
        $userId      = (int)($_GET['id'] ?? 0);
        $currentUser = $this->getCurrentUserData($userId);

        if (!$this->userModel->findById($userId)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Usuário não encontrado.'];
            $this->redirect('feed');
            return;
        }

        $posts       = $this->hydratePostsWithComments($this->postModel->getByUser($userId, $viewerId), $viewerId);
        $followModel = $this->followModel;

        require __DIR__ . '/../views/profile.php';
    }

    private function showInstitutionProfile(): void
    {
        $institutionId = (int)($_GET['id'] ?? 0);
        $institution   = $this->userModel->findById($institutionId);

        if (!$institution || $institution['user_type'] !== 'institution') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Instituição não encontrada.'];
            $this->redirect('feed');
            return;
        }

        $posts = $this->hydratePostsWithComments($this->postModel->getByUser($institutionId), $institutionId);

        require __DIR__ . '/../views/institution_profile.php';
    }

    private function profileUpdate(): void
    {
        $id           = (int)$_SESSION['user_id'];
        $name         = trim($_POST['name']       ?? '');
        $email        = trim($_POST['email']      ?? '');
        $password     = trim($_POST['password']   ?? '');
        $bio          = trim($_POST['bio']        ?? '');
        $avatarUrl    = trim($_POST['avatar_url'] ?? '');
        $extraInfo    = trim($_POST['extra_info'] ?? '');
        $currentUser  = $this->userModel->findById($id);
        $uploadedAvatarUrl = $this->handleUpload('avatar_file');
        $avatarUrl = $uploadedAvatarUrl !== '' ? $uploadedAvatarUrl : $avatarUrl;

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nome e e-mail são obrigatórios.'];
            $this->redirect('edit_profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'E-mail inválido.'];
            $this->redirect('edit_profile');
            return;
        }

        if ($this->userModel->emailExistsForOtherUser($email, $id)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Este e-mail já está em uso.'];
            $this->redirect('edit_profile');
            return;
        }

        $passwordHash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

        $this->userModel->updateAccount(
            $id,
            $name,
            $email,
            $passwordHash,
            $bio,
            $avatarUrl,
            $extraInfo
        );

        $_SESSION['user_name'] = $name;
        $_SESSION['flash']     = ['type' => 'success', 'msg' => 'Dados da conta atualizados com sucesso.'];
        $this->redirect('edit_profile');
    }

    private function profileDelete(): void
    {
        $id = (int)$_SESSION['user_id'];

        if ($this->userModel->deleteById($id)) {
            session_unset();
            session_destroy();
            header('Location: index.php?action=login');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Não foi possível excluir a conta no momento.'];
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

    private function getCurrentUserData(int $userId): array
    {
        $user = $this->userModel->findById($userId);

        if (!$user) {
            return [
                'id' => $userId,
                'name' => 'Usuário',
                'email' => '',
                'bio' => '',
                'avatar_url' => '',
                'user_type' => 'student',
                'extra_info' => '',
            ];
        }

        return $user;
    }

    private function hydratePostsWithComments(array $posts, int $viewerId = 0): array
    {
        foreach ($posts as &$post) {
            $post['comments']      = $this->commentModel->getByPost((int)($post['id'] ?? 0));
            $post['comment_count'] = count($post['comments']);
            $post['liked_by_me']   = (int)($post['liked_by_me'] ?? 0) > 0;
        }
        unset($post);

        return $posts;
    }

    private function fetchGoogleToken(string $code): array|false
    {
        $clientId     = $this->getGoogleClientId();
        $clientSecret = $this->getGoogleClientSecret();

        if ($clientId === '' || $clientSecret === '') {
            return false;
        }

        $postData = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $this->getGoogleRedirectUri(),
            'grant_type'    => 'authorization_code',
        ];

        return $this->httpPostForm(self::GOOGLE_OAUTH_TOKEN_URL, $postData);
    }

    private function fetchGoogleUserInfo(string $accessToken): array|false
    {
        $url = self::GOOGLE_OAUTH_USERINFO_URL;
        $options = [
            'http' => [
                'method'  => 'GET',
                'header'  => "Authorization: Bearer {$accessToken}\r\n",
                'timeout' => 10,
            ],
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        return $response ? json_decode($response, true) : false;
    }

    private function httpPostForm(string $url, array $postData): array|false
    {
        $body = http_build_query($postData, '', '&', PHP_QUERY_RFC1738);

        if (function_exists('curl_version')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response ? json_decode($response, true) : false;
        }

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                'content' => $body,
                'timeout' => 10,
            ],
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        return $response ? json_decode($response, true) : false;
    }

    private function getGoogleClientId(): string
    {
        return trim((string) getenv('GOOGLE_CLIENT_ID')) ?: self::GOOGLE_CLIENT_ID;
    }

    private function getGoogleClientSecret(): string
    {
        return trim((string) getenv('GOOGLE_CLIENT_SECRET')) ?: self::GOOGLE_CLIENT_SECRET;
    }

    private function getGoogleRedirectUri(): string
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $basePath = $scriptDir !== '' ? '/' . $scriptDir : '';
        $path = '/' . ltrim(self::GOOGLE_REDIRECT_PATH, '/');

        return sprintf('%s://%s%s%s', $scheme, $host, $basePath, $path);
    }

    private function redirect(string $action): void
    {
        header('Location: index.php?action=' . $action);
        exit;
    }
}
