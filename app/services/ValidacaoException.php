<?php
declare(strict_types=1);

/**
 * Erro de validação de dados informados pelo usuário.
 *
 * Carrega as mensagens por campo, ex.: ['email' => 'Informe um e-mail válido.'],
 * para a view exibir cada mensagem junto do campo correspondente.
 */
class ValidacaoException extends RuntimeException
{
    /** @var array<string, string> */
    private array $erros;

    /** @param array<string, string> $erros */
    public function __construct(array $erros)
    {
        parent::__construct('Dados inválidos.');
        $this->erros = $erros;
    }

    /** @return array<string, string> */
    public function getErros(): array
    {
        return $this->erros;
    }
}
