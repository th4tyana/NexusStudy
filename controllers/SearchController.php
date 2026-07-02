<?php
declare(strict_types=1);

class SearchController
{
    public function __construct(
        private UserDAO $userDAO,
        private PostDAO $postDAO,
        private CommentDAO $commentDAO,
        private $mainController
    ) {
    }

    public function searchGlobal(): void
    {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $searchQuery   = trim($_GET['q'] ?? $_POST['q'] ?? '');
        $searchResults = [];
        $posts         = $this->hydratePostsWithComments($this->postDAO->getAll($currentUserId), $currentUserId);
        $currentUser   = $this->mainController->getCurrentUserData($currentUserId);

        if ($searchQuery !== '') {
            $searchResults = $this->userDAO->searchPeople($searchQuery);
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

        $results = $this->userDAO->searchPeople($term);
        echo json_encode($results);
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
