<?php
declare(strict_types=1);

class AuthController
{
    private const GOOGLE_OAUTH_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_OAUTH_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    private const GOOGLE_OAUTH_SCOPE = 'openid email profile';
    private const GOOGLE_REDIRECT_PATH = 'index.php?action=google_callback';
    private const GOOGLE_CLIENT_ID = '661857518557-j7ke43j0ntsh0glkn4smoagrt69798i8.apps.googleusercontent.com';
    private const GOOGLE_CLIENT_SECRET = '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';

    public function __construct(
        private UserDAO $userDAO,
        private MainController $mainController
    ) {
    }

    public function showAuth(): void
    {
        if ($this->mainController->isLoggedIn()) {
            $this->mainController->redirect('feed');
        }
        require __DIR__ . '/../views/auth.php';
    }

    public function doLogin(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = '';

        if (empty($email) || empty($password)) {
            $error = 'Preencha todos os campos.';
        } else {
            $user = $this->userDAO->findByEmail($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                $this->mainController->redirect('feed');
                return;
            }
            $error = 'E-mail ou senha incorretos.';
        }
        require __DIR__ . '/../views/auth.php';
    }

    public function doRegister(): void
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
        } elseif ($this->userDAO->emailExists($email)) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            $id = $this->userDAO->create($name, $email, $password, $userType, $extraInfo);
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_type'] = $userType;
            $this->mainController->redirect('feed');
            return;
        }
        require __DIR__ . '/../views/auth.php';
    }

    public function doGoogleLogin(): void
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

    public function doGoogleCallback(): void
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

        $user = $this->userDAO->findByEmail($email);
        if (!$user) {
            $password = bin2hex(random_bytes(8));
            $userId = $this->userDAO->create($name, $email, $password, 'student', '');
            $user = $this->userDAO->findById($userId);
        }

        if (!$user) {
            $error = 'Falha ao criar ou carregar usuário após autenticação do Google.';
            require __DIR__ . '/../views/auth.php';
            return;
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        $this->mainController->redirect('feed');
    }

    public function doLogout(): void
    {
        session_destroy();
        $this->mainController->redirect('login');
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
}
