<?php
/**
 * EduConnect - Configuração de Conexão com o Banco de Dados
 * 
 * Implementação profissional utilizando o padrão Singleton.
 * Esta classe garante que apenas uma conexão com o banco de dados 
 * seja aberta durante todo o ciclo de vida da requisição.
 */

// Configurações de conexão - Em um ambiente de produção, estas variáveis 
// deveriam estar em um arquivo .env fora da pasta pública do servidor.
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_de_chat_educacional');
define('DB_USER', 'root');
define('DB_PASS', '');     
define('DB_CHARSET', 'utf8mb4');

class Database
{
    /** @var PDO|null Instância única da conexão */
    private static ?PDO $instance = null;

    /**
     * Construtor privado para impedir a instanciação externa (Singleton)
     */
    private function __construct() {}

    /**
     * Impede a clonagem da instância
     */
    private function __clone() {}

    /**
     * Retorna a instância única da conexão PDO.
     * 
     * @return PDO
     * @throws PDOException Caso a conexão falhe
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST, 
                    DB_NAME, 
                    DB_CHARSET
                );

                $options = [
                    // Lança exceções em caso de erro SQL (Essencial para debug)
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Retorna os dados como array associativo por padrão
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Desativa a emulação de prepared statements para maior segurança
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    // Garante que a conexão utilize a codificação correta
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);

            } catch (PDOException $e) {
                // Log de erro interno (em produção, use error_log)
                error_log("Erro de Conexão DB: " . $e->getMessage());

                // Resposta amigável para o usuário final
                http_response_code(500);
                die("Erro interno: Não foi possível conectar ao banco de dados. Verifique se o MySQL está ativo.");
            }
        }
        return self::$instance;
    }
}
