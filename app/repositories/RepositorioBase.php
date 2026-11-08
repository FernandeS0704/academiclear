<?php
declare(strict_types=1);

/**
 * Classe base dos repositories.
 *
 * Guarda a conexão PDO compartilhada e concentra a execução de SQL em um
 * único ponto, para que o tratamento de exceções não se repita nos repositories.
 */
abstract class RepositorioBase
{
    /** Código de erro do MySQL/MariaDB para "Duplicate entry". */
    private const ERRO_MYSQL_DUPLICADO = 1062;

    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::obter();
    }

    /**
     * Prepara e executa um SQL com parâmetros (prepared statement).
     *
     * @throws RegistroDuplicadoException se uma restrição UNIQUE for violada
     * @throws RepositorioException       para qualquer outro erro de banco
     */
    protected function executar(string $sql, array $parametros = [], ?string $mensagemDuplicado = null): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($parametros);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Erro de banco em ' . static::class . ': ' . $e->getMessage());

            if (($e->errorInfo[1] ?? null) === self::ERRO_MYSQL_DUPLICADO) {
                throw new RegistroDuplicadoException(
                    $mensagemDuplicado ?? 'Já existe um registro com esses dados.',
                    0,
                    $e
                );
            }

            throw new RepositorioException('Erro ao acessar o banco de dados.', 0, $e);
        }
    }

    /** Executa uma consulta e devolve a primeira linha, ou null se não houver. */
    protected function buscarLinha(string $sql, array $parametros = []): ?array
    {
        $linha = $this->executar($sql, $parametros)->fetch();
        return $linha === false ? null : $linha;
    }

    /** Executa uma consulta e devolve todas as linhas. */
    protected function buscarLinhas(string $sql, array $parametros = []): array
    {
        return $this->executar($sql, $parametros)->fetchAll();
    }
}
