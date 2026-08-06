<?php
declare(strict_types=1);

class PostController
{
    private const UPLOAD_DIR = __DIR__ . '/../uploads';
    private const UPLOAD_URL = 'uploads';
    private const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;

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
            $pdfUrl     = $this->handlePdfUpload('pdf_file');

            if (!empty($content) && !empty($courseName)) {
                $this->postDAO->createStudyGuide($userId, $content, $courseName, $entryType, $weights, $pdfUrl);
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
        $postId     = (int)($_POST['post_id'] ?? 0);
        $content    = trim($_POST['content'] ?? '');
        $existing   = trim($_POST['existing_media_url'] ?? '');
        $post       = $this->postDAO->findById($postId);
        $uploadUrl  = $this->handleUpload('media_file');
        $mediaUrl   = $uploadUrl !== '' ? $uploadUrl : $existing;

        if (!$post || !$this->canModifyPost($post)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Sem permissão.'];
        } elseif (!empty($content)) {
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

    private function handleUpload(string $fieldName): string
    {
        if (empty($_FILES[$fieldName]['name']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $file = $_FILES[$fieldName];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return '';
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $fileName    = sprintf('img_%s.%s', bin2hex(random_bytes(8)), $ext);
        $destination = self::UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return self::UPLOAD_URL . '/' . $fileName;
        }

        return '';
    }

    private function handlePdfUpload(string $fieldName): string
    {
        if (empty($_FILES[$fieldName]['name']) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $file = $_FILES[$fieldName];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Apenas arquivos PDF são permitidos para o edital.'];
            return '';
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $fileName    = sprintf('edital_%s.pdf', bin2hex(random_bytes(8)));
        $destination = self::UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return self::UPLOAD_URL . '/' . $fileName;
        }

        return '';
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