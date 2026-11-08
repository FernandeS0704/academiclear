<?php
declare(strict_types=1);

/**
 * Regras de negócio da autenticação: valida o cadastro e confere credenciais.
 *
 * Não conhece sessão, HTML nem redirecionamentos (isso é do Controller).
 * A persistência é feita pelo UsuarioRepository já existente.
 */
class AutenticacaoService
{
    public const SENHA_MINIMA = 8;
    public const SENHA_MAXIMA = 72; // limite do bcrypt usado pelo password_hash()
    private const NOME_MAXIMO = 100;
    private const EMAIL_MAXIMO = 150;

    private static ?string $hashFalso = null;

    private UsuarioRepository $usuarios;

    public function __construct(?UsuarioRepository $usuarios = null)
    {
        $this->usuarios = $usuarios ?? new UsuarioRepository();
    }

    /**
     * Valida os dados e cadastra o usuário (senha gravada com password_hash()).
     *
     * @throws ValidacaoException com as mensagens por campo
     */
    public function cadastrar(string $nome, string $email, string $senha, string $confirmacao): Usuario
    {
        $erros = $this->validarCadastro(trim($nome), trim($email), $senha, $confirmacao);
        if ($erros !== []) {
            throw new ValidacaoException($erros);
        }

        try {
            return $this->usuarios->cadastrar(Usuario::novo($nome, $email, $senha));
        } catch (RegistroDuplicadoException) {
            throw new ValidacaoException(['email' => 'Este e-mail já está cadastrado.']);
        }
    }

    /**
     * Confere e-mail e senha.
     *
     * @return Usuario|null o usuário autenticado, ou null se as credenciais forem inválidas.
     *                      O retorno é o mesmo para "e-mail inexistente" e "senha errada",
     *                      para não revelar quais e-mails estão cadastrados.
     */
    public function autenticar(string $email, string $senha): ?Usuario
    {
        $usuario = trim($email) !== '' ? $this->usuarios->buscarPorEmail($email) : null;

        // Mesmo sem usuário, executa um password_verify() para o tempo de resposta
        // não denunciar se o e-mail existe.
        $hash = $usuario !== null ? $usuario->getSenha() : self::hashFalso();
        $senhaConfere = password_verify($senha, $hash);

        return ($usuario !== null && $senhaConfere) ? $usuario : null;
    }

    /** @return array<string, string> */
    private function validarCadastro(string $nome, string $email, string $senha, string $confirmacao): array
    {
        $erros = [];

        if ($nome === '') {
            $erros['nome'] = 'Informe seu nome completo.';
        } elseif (mb_strlen($nome) > self::NOME_MAXIMO) {
            $erros['nome'] = 'O nome deve ter no máximo ' . self::NOME_MAXIMO . ' caracteres.';
        }

        if ($email === '') {
            $erros['email'] = 'Informe seu e-mail.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        } elseif (mb_strlen($email) > self::EMAIL_MAXIMO) {
            $erros['email'] = 'O e-mail deve ter no máximo ' . self::EMAIL_MAXIMO . ' caracteres.';
        }

        if ($senha === '') {
            $erros['senha'] = 'Informe uma senha.';
        } elseif (mb_strlen($senha) < self::SENHA_MINIMA) {
            $erros['senha'] = 'A senha deve ter pelo menos ' . self::SENHA_MINIMA . ' caracteres.';
        } elseif (strlen($senha) > self::SENHA_MAXIMA) {
            $erros['senha'] = 'A senha deve ter no máximo ' . self::SENHA_MAXIMA . ' bytes.';
        }

        if ($confirmacao === '') {
            $erros['confirmacao'] = 'Confirme sua senha.';
        } elseif (!isset($erros['senha']) && $senha !== $confirmacao) {
            $erros['confirmacao'] = 'As senhas não conferem.';
        }

        return $erros;
    }

    private static function hashFalso(): string
    {
        return self::$hashFalso ??= password_hash('senha-inexistente', PASSWORD_DEFAULT);
    }
}
