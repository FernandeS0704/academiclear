<?php
declare(strict_types=1);

/**
 * Erro genérico da camada de repositories.
 *
 * Encapsula a PDOException original (acessível por getPrevious()) sem expor
 * detalhes do banco (host, usuário, SQL) para quem usa o repository.
 */
class RepositorioException extends RuntimeException
{
}
