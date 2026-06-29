<?php
/**
 * EduConnect — Front Controller
 * Ponto único de entrada da aplicação (index.php).
 * Toda requisição passa por aqui antes de ser despachada
 * para o MainController.
 *
 * Estrutura MVC:
 *   index.php  →  MainController  →  Models  →  Views
 *
 * Requisitos:
 *   - PHP 8.1+
 *   - Extensão PDO + PDO_MySQL habilitada
 *   - MySQL/MariaDB com o schema.sql importado
 */

declare(strict_types=1);

// Iniciar sessão antes de qualquer output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload manual dos componentes
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Models.php';
require_once __DIR__ . '/controllers/MainController.php';

// Instanciar e despachar o controller principal
$controller = new MainController();
$controller->dispatch();
