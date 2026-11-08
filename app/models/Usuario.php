<?php
declare(strict_types=1);

/**
 * Model Usuario: representa um registro da tabela "usuario".
 *
 * Guarda apenas dados e regras simples de validação.
 * O acesso ao banco fica em UsuarioRepository.
 */
class Usuario
{
    private ?int $idUsuario;
    private string $nome;
    private string $email;
    private string $senha; // sempre o HASH, nunca a senha em texto puro

    public function __construct(?int $idUsuario, string $nome, string $email, string $senha)
    {
        $this->idUsuario = $idUsuario;
        $this->setNome($nome);
        $this->setEmail($email);
        $this->senha = $senha;
    }

    /** Cria um usuário novo a partir de uma senha em texto puro, já gerando o hash. */
    public static function novo(string $nome, string $email, string $senhaPura): self
    {
        if (strlen($senhaPura) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }

        return new self(null, $nome, $email, password_hash($senhaPura, PASSWORD_DEFAULT));
    }

    public static function deArray(array $linha): self
    {
        return new self(
            (int) $linha['id_usuario'],
            $linha['nome'],
            $linha['email'],
            $linha['senha']
        );
    }

    public function verificarSenha(string $senhaPura): bool
    {
        return password_verify($senhaPura, $this->senha);
    }

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function setIdUsuario(int $idUsuario): void
    {
        $this->idUsuario = $idUsuario;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new InvalidArgumentException('O nome do usuário é obrigatório.');
        }
        $this->nome = $nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
        $this->email = $email;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    /** Define uma nova senha a partir do texto puro, armazenando somente o hash. */
    public function alterarSenha(string $senhaPura): void
    {
        if (strlen($senhaPura) < 6) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres.');
        }
        $this->senha = password_hash($senhaPura, PASSWORD_DEFAULT);
    }
}
