<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/DAO/UserDAO.php';
require_once __DIR__ . '/../models/DAO/PostDAO.php';
require_once __DIR__ . '/../models/DAO/LikeDAO.php';
require_once __DIR__ . '/../models/DAO/CommentDAO.php';
require_once __DIR__ . '/../models/DAO/FollowDAO.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/PostController.php';
require_once __DIR__ . '/ProfileController.php';
require_once __DIR__ . '/SearchController.php';

/**
 * EduConnect - MainController
 * Front Controller responsável apenas pelo roteamento e injeção das dependências.
 */
class MainController
{
    public UserDAO $userDAO;
    public PostDAO $postDAO;
    public LikeDAO $likeDAO;
    public CommentDAO $commentDAO;
    public FollowDAO $followDAO;

    private AuthController $authController;
    private PostController $postController;
    private ProfileController $profileController;
    private SearchController $searchController;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userDAO    = new UserDAO();
        $this->postDAO    = new PostDAO();
        $this->likeDAO    = new LikeDAO();
        $this->commentDAO = new CommentDAO();
        $this->followDAO  = new FollowDAO();

        $this->authController    = new AuthController($this->userDAO, $this);
        $this->postController    = new PostController($this->postDAO, $this->commentDAO, $this->likeDAO, $this);
        $this->profileController = new ProfileController($this->userDAO, $this->postDAO, $this->commentDAO, $this->followDAO, $this);
        $this->searchController  = new SearchController($this->userDAO, $this->postDAO, $this->commentDAO, $this);
    }

    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'feed';

        if ($action === 'login')    { $this->authController->showAuth(); return; }
        if ($action === 'register') { $this->authController->showAuth(); return; }

        if ($action === 'do_login')       { $this->authController->doLogin(); return; }
        if ($action === 'do_register')    { $this->authController->doRegister(); return; }
        if ($action === 'google_login')   { $this->authController->doGoogleLogin(); return; }
        if ($action === 'google_callback') { $this->authController->doGoogleCallback(); return; }

        if ($action === 'institution_profile') { $this->profileController->showInstitutionProfile(); return; }
        if ($action === 'user_profile') { $this->profileController->showUserProfile(); return; }

        $this->requireLogin();

        match ($action) {
            'feed'                => $this->postController->showFeed(),
            'search_global'       => $this->searchController->searchGlobal(),
            'search'              => $this->searchController->searchGlobal(),
            'search_autocomplete' => $this->searchController->searchAutocomplete(),
            'toggle_follow'       => $this->postController->toggleFollow(),
            'follow_list'         => $this->postController->followList(),
            'post_create'         => $this->postController->postCreate(),
            'post_edit'           => $this->postController->showEditPost(),
            'post_update'         => $this->postController->postUpdate(),
            'post_delete'         => $this->postController->postDelete(),
            'like_toggle'         => $this->postController->likeToggle(),
            'comment_create'      => $this->postController->commentCreate(),
            'profile'             => $this->profileController->showProfile(),
            'edit_profile'        => $this->profileController->showEditProfile(),
            'profile_update'      => $this->profileController->profileUpdate(),
            'profile_delete'      => $this->profileController->profileDelete(),
            'logout'              => $this->authController->doLogout(),
            default               => $this->postController->showFeed(),
        };
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }

    public function getCurrentUserData(int $userId): array
    {
        $user = $this->userDAO->findById($userId);

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

    public function redirect(string $action): void
    {
        header('Location: index.php?action=' . $action);
        exit;
    }
}
