<?php
declare(strict_types=1);

/**
 * Model Disciplina: representa um registro da tabela "disciplina".
 */
class Disciplina
{
    private ?int $idDisciplina;
    private string $nome;
    private int $idUsuario;

    public function __construct(?int $idDisciplina, string $nome, int $idUsuario)
    {
        $this->idDisciplina = $idDisciplina;
        $this->setNome($nome);
        $this->idUsuario = $idUsuario;
    }

    public static function deArray(array $linha): self
    {
        return new self(
            (int) $linha['id_disciplina'],
            $linha['nome'],
            (int) $linha['id_usuario']
        );
    }

    public function getIdDisciplina(): ?int
    {
        return $this->idDisciplina;
    }

    public function setIdDisciplina(int $idDisciplina): void
    {
        $this->idDisciplina = $idDisciplina;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new InvalidArgumentException('O nome da disciplina é obrigatório.');
        }
        $this->nome = $nome;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }
}
