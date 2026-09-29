<?php
declare(strict_types=1);

class PostController
{
    private const UPLOAD_DIR = __DIR__ . '/../uploads';
    private const UPLOAD_URL = 'uploads';

    public function __construct(
        private PostDAO $postDAO,
        private CommentDAO $commentDAO,
        private LikeDAO $likeDAO,
        private $mainController
    ) {
    }

    public function bolsasGuide(): void 
    {
        $guides = $this->postDAO->getStudyGuides();
        require_once __DIR__ . '/../views/bolsasguide.php';
    }

    public function showFeed(): void
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $posts         = $this->hydratePostsWithComments($this->postDAO->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->mainController->getCurrentUserData($currentUserId);
        $searchQuery   = '';
        $searchResults = [];

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/feed_view.php';
    }

    public function searchGlobal(): void
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $searchQuery   = trim($_GET['q'] ?? $_POST['q'] ?? '');
        $searchResults = [];
        $posts         = $this->hydratePostsWithComments($this->postDAO->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->mainController->getCurrentUserData($currentUserId);

        if ($searchQuery !== '') {
            $searchResults = $this->mainController->userDAO->searchPeople($searchQuery);
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/feed_view.php';
    }

    public function searchAutocomplete(): void
    {
        $term = trim($_GET['q'] ?? $_POST['q'] ?? '');
        if ($term === '') {
            echo json_encode([]);
            return;
        }

        $results = $this->mainController->userDAO->searchPeople($term);
        echo json_encode($results);
    }

    public function toggleFollow(): void
    {
        $viewerId = (int)($_SESSION['user_id'] ?? 0);
        $targetId = (int)($_POST['target_id'] ?? 0);

        if ($viewerId <= 0 || $targetId <= 0 || $viewerId === $targetId) {
            echo json_encode(['success' => false]);
            return;
        }

        $followDAO = $this->mainController->followDAO;
        $isFollowing = $followDAO->isFollowing($viewerId, $targetId);
        if ($isFollowing) {
            $followDAO->unfollow($viewerId, $targetId);
            echo json_encode(['success' => true, 'following' => false]);
        } else {
            $followDAO->follow($viewerId, $targetId);
            echo json_encode(['success' => true, 'following' => true]);
        }
    }

    public function followList(): void
    {
        $viewerId = (int)($_SESSION['user_id'] ?? 0);
        $userId   = (int)($_GET['id'] ?? 0);
        $type     = $_GET['type'] ?? 'followers';

        if ($userId <= 0) {
            echo '';
            return;
        }

        $followDAO = $this->mainController->followDAO;
        $items = $type === 'following'
            ? $followDAO->getFollowing($userId)
            : $followDAO->getFollowers($userId);

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

    public function postCreate(): void
    {
        $userId     = (int)($_SESSION['user_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');
        $redirectTo = in_array($_POST['redirect_to'] ?? '', ['feed', 'profile'], true) ? $_POST['redirect_to'] : 'feed';
        $isGuide    = !empty($_POST['is_study_guide']);

        if ($isGuide) {
            $courseName = trim($_POST['course_name'] ?? '');
            $entryType  = trim($_POST['entry_type'] ?? '');
            $weights    = json_encode($_POST['weights'] ?? []);
            $docUrl     = $this->handleDocumentUpload('pdf_file');

            if ($docUrl === '') {
                // Arquivo rejeitado (ou ausente): NÃO cria o guia e preserva a mensagem de erro da validação.
                if (empty($_SESSION['flash'])) {
                    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Anexe o documento do edital (PDF, DOCX ou XLSX).'];
                }
            } elseif (!empty($content) && !empty($courseName)) {
                $this->postDAO->createStudyGuide($userId, $content, $courseName, $entryType, $weights, $docUrl);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Guia de estudos publicado com sucesso!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Preencha todos os campos do guia.'];
            }
        } else {
            $mediaUrl = $this->handleUpload('media_file');
            if (!empty($content)) {
                $this->postDAO->create($userId, $content, $mediaUrl);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação criada com sucesso!'];
            }
        }

        $this->mainController->redirect($redirectTo);
    }

    public function showEditPost(): void
    {
        $postId = (int)($_GET['id'] ?? 0);
        $post   = $this->postDAO->findById($postId);

        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão para editar esta publicação.'];
            $this->mainController->redirect('feed');
            return;
        }

        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $posts         = $this->hydratePostsWithComments($this->postDAO->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->mainController->getCurrentUserData($currentUserId);
        $searchQuery   = '';
        $searchResults = [];

        require __DIR__ . '/../views/feed_view.php';
    }

    public function postUpdate(): void
    {
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $post    = $this->postDAO->findById($postId);

        // Autorização ANTES de tocar no disco: quem não pode editar não grava arquivo nenhum.
        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão.'];
            $this->mainController->redirect('feed');
            return;
        }

        // A mídia atual vem do banco, nunca do POST (evita apontar media_url para um arquivo não validado).
        $existing  = (string)($post['media_url'] ?? '');
        $uploadUrl = $this->handleUpload('media_file');
        $mediaUrl  = $uploadUrl !== '' ? $uploadUrl : $existing;

        if (!empty($content)) {
            $this->postDAO->update($postId, $content, $mediaUrl);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação atualizada.'];
        }
        $this->mainController->redirect('feed');
    }

    public function postDelete(): void
    {
        $postId = (int)($_GET['id'] ?? 0);
        $post   = $this->postDAO->findById($postId);

        if ($post && $this->canModifyPost($post)) {
            $this->postDAO->delete($postId);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Publicação removida.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão para remover.'];
        }
        $this->mainController->redirect('feed');
    }

    public function likeToggle(): void
    {
        $postId = (int)($_POST['post_id'] ?? 0);
        $result = $this->likeDAO->toggle($postId, (int)$_SESSION['user_id']);
        $count  = $this->likeDAO->countForPost($postId);

        header('Content-Type: application/json');
        echo json_encode(['action' => $result, 'count' => $count]);
        exit;
    }

    public function commentCreate(): void
    {
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content']  ?? '');

        if (!empty($content)) {
            $result = $this->commentDAO->create($postId, (int)$_SESSION['user_id'], $content);
            if (!$result['success']) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => $result['message']];
            }
        }
        $this->mainController->redirect('feed');
    }

    /** Upload de imagem (mídia de publicação). */
    private function handleUpload(string $fieldName): string
    {
        return $this->processUpload($fieldName, FileUploadValidator::PROFILE_IMAGE, 'img_');
    }

    /** Upload de documento do edital/guia: PDF, DOCX ou XLSX (Google Docs/Sheets exportados). */
    private function handleDocumentUpload(string $fieldName): string
    {
        return $this->processUpload($fieldName, FileUploadValidator::PROFILE_DOCUMENT, 'edital_');
    }

    /**
     * Ponto único de upload do controller. Toda a validação (extensão, MIME real,
     * content-sniffing, conteúdo ativo, tamanho) fica em FileUploadValidator.
     * Retorna a URL pública do arquivo salvo ou '' (com flash de erro, se aplicável).
     */
    private function processUpload(string $fieldName, string $profile, string $prefix): string
    {
        $file = $_FILES[$fieldName] ?? null;

        // Nenhum arquivo enviado: não é erro (campo opcional).
        if (!is_array($file) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        $result = FileUploadValidator::storeUpload($file, $profile, self::UPLOAD_DIR, $prefix);

        if (!$result['ok']) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $result['error']];
            return '';
        }

        return self::UPLOAD_URL . '/' . $result['file_name'];
    }

    private function canModifyPost(array $post): bool
    {
        $uid  = (int) ($_SESSION['user_id']   ?? 0);
        $type = $_SESSION['user_type'] ?? '';
        return ($post['user_id'] == $uid) && ($type === 'institution');
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