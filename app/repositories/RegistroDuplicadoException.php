<?php
declare(strict_types=1);

/**
 * Lançada quando uma restrição UNIQUE do banco é violada
 * (ex.: e-mail já cadastrado, disciplina com nome repetido para o mesmo usuário).
 */
class RegistroDuplicadoException extends RepositorioException
{
}
