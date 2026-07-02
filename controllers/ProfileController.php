<?php
declare(strict_types=1);

class ProfileController
{
    private const UPLOAD_DIR = __DIR__ . '/../uploads';
    private const UPLOAD_URL = 'uploads';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;

    public function __construct(
        private UserDAO $userDAO,
        private PostDAO $postDAO,
        private CommentDAO $commentDAO,
        private FollowDAO $followDAO,
        private $mainController
    ) {
    }

    public function showProfile(): void
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUser   = $this->mainController->getCurrentUserData($currentUserId);
        $posts         = $this->hydratePostsWithComments($this->postDAO->getByUser($currentUserId), $currentUserId);
        $followModel   = $this->followDAO;

        require __DIR__ . '/../views/profile.php';
    }

    public function showEditProfile(): void
    {
        $currentUser = $this->mainController->getCurrentUserData((int)($_SESSION['user_id'] ?? 0));
        require __DIR__ . '/../views/edit_profile.php';
    }

    public function showUserProfile(): void
    {
        $viewerId    = (int)($_SESSION['user_id'] ?? 0);
        $userId      = (int)($_GET['id'] ?? 0);
        $currentUser = $this->mainController->getCurrentUserData($userId);

        if (!$this->userDAO->findById($userId)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Usuário não encontrado.'];
            $this->mainController->redirect('feed');
            return;
        }

        $posts       = $this->hydratePostsWithComments($this->postDAO->getByUser($userId, $viewerId), $viewerId);
        $followModel = $this->followDAO;

        require __DIR__ . '/../views/profile.php';
    }

    public function showInstitutionProfile(): void
    {
        $institutionId = (int)($_GET['id'] ?? 0);
        $institution   = $this->userDAO->findById($institutionId);

        if (!$institution || $institution['user_type'] !== 'institution') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Instituição não encontrada.'];
            $this->mainController->redirect('feed');
            return;
        }

        $posts = $this->hydratePostsWithComments($this->postDAO->getByUser($institutionId), $institutionId);

        require __DIR__ . '/../views/institution_profile.php';
    }

    public function profileUpdate(): void
    {
        $id           = (int)$_SESSION['user_id'];
        $name         = trim($_POST['name']       ?? '');
        $email        = trim($_POST['email']      ?? '');
        $password     = trim($_POST['password']   ?? '');
        $bio          = trim($_POST['bio']        ?? '');
        $avatarUrl    = trim($_POST['avatar_url'] ?? '');
        $extraInfo    = trim($_POST['extra_info'] ?? '');
        $uploadedAvatarUrl = $this->handleUpload('avatar_file');
        $avatarUrl = $uploadedAvatarUrl !== '' ? $uploadedAvatarUrl : $avatarUrl;

        if (empty($name) || empty($email)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nome e e-mail são obrigatórios.'];
            $this->mainController->redirect('edit_profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'E-mail inválido.'];
            $this->mainController->redirect('edit_profile');
            return;
        }

        if ($this->userDAO->emailExistsForOtherUser($email, $id)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Este e-mail já está em uso.'];
            $this->mainController->redirect('edit_profile');
            return;
        }

        $passwordHash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

        $this->userDAO->updateAccount(
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
        $this->mainController->redirect('edit_profile');
    }

    public function profileDelete(): void
    {
        $id = (int)$_SESSION['user_id'];

        if ($this->userDAO->deleteById($id)) {
            session_unset();
            session_destroy();
            header('Location: index.php?action=login');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Não foi possível excluir a conta no momento.'];
        $this->mainController->redirect('edit_profile');
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

    private function hydratePostsWithComments(array $posts, int $viewerId = 0): array
    {
        foreach ($posts as &$post) {
            $post['comments']      = $this->commentDAO->getByPost((int)($post['id'] ?? 0));
            $post['comment_count'] = count($post['comments']);
            $post['liked_by_me']   = (int)($post['liked_by_me'] ?? 0) > 0;
        }
        unset($post);

        return $posts;
    }
}
