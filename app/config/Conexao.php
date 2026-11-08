<?php
declare(strict_types=1);

/**
 * Conexão centralizada com o MySQL usando PDO.
 *
 * - Padrão Singleton: a mesma conexão é reaproveitada em todo o sistema.
 * - Credenciais lidas de app/config/config.local.php (fora do Git).
 * - PDO::ERRMODE_EXCEPTION: erros de banco viram exceções (PDOException).
 * - Prepared statements reais (EMULATE_PREPARES = false) contra SQL Injection.
 *
 * Uso:
 *   $pdo = Conexao::obter();
 */
class Conexao
{
    private static ?PDO $instancia = null;

    // Impede instanciar a classe: use Conexao::obter().
    private function __construct()
    {
    }

    public static function obter(): PDO
    {
        if (self::$instancia === null) {
            self::$instancia = self::criar();
        }

        return self::$instancia;
    }

    private static function criar(): PDO
    {
        $config = self::carregarConfiguracao()['banco'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['porta'],
            $config['nome'],
            $config['charset']
        );

        try {
            return new PDO($dsn, $config['usuario'], $config['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Não repassamos a mensagem original: ela pode expor host/usuário.
            error_log('Falha na conexão com o banco: ' . $e->getMessage());
            throw new RuntimeException(
                'Não foi possível conectar ao banco de dados. Verifique o MySQL no XAMPP e o arquivo config.local.php.'
            );
        }
    }

    private static function carregarConfiguracao(): array
    {
        $arquivo = __DIR__ . '/config.local.php';

        if (!is_file($arquivo)) {
            throw new RuntimeException(
                'Arquivo app/config/config.local.php não encontrado. Copie config.exemplo.php para config.local.php.'
            );
        }

        return require $arquivo;
    }
}
