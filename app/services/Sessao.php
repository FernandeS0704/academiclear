<?php
declare(strict_types=1);

/**
 * Controle centralizado da sessão PHP.
 *
 * Responsabilidades:
 *  - iniciar a sessão com cookie seguro (HttpOnly, SameSite);
 *  - guardar na sessão SOMENTE o id e o nome do usuário autenticado;
 *  - encerrar a sessão por completo (logout);
 *  - mensagens "flash" (aparecem uma única vez após um redirecionamento);
 *  - token CSRF para proteger os formulários.
 *
 * Nenhum outro arquivo deve mexer em $_SESSION diretamente.
 */
class Sessao
{
    private const NOME_SESSAO = 'ACADEMICLEAR_SESSAO';
    private const CHAVE_USUARIO = 'usuario';
    private const CHAVE_CSRF = 'csrf_token';
    private const CHAVE_FLASH = 'flash';

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(self::NOME_SESSAO);
        ini_set('session.use_strict_mode', '1'); // rejeita IDs de sessão não criados pelo servidor
        session_set_cookie_params([
            'lifetime' => 0,           // expira ao fechar o navegador
            'path'     => '/',
            'secure'   => self::conexaoSegura(),
            'httponly' => true,        // JavaScript não lê o cookie
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /** Registra o usuário como autenticado. Deve ser chamado logo após validar a senha. */
    public static function autenticar(Usuario $usuario): void
    {
        self::iniciar();
        session_regenerate_id(true); // evita fixação de sessão
        unset($_SESSION[self::CHAVE_CSRF]);

        $_SESSION[self::CHAVE_USUARIO] = [
            'id'   => $usuario->getIdUsuario(),
            'nome' => $usuario->getNome(),
        ];
    }

    public static function estaAutenticado(): bool
    {
        self::iniciar();
        return isset($_SESSION[self::CHAVE_USUARIO]['id']);
    }

    public static function idUsuario(): ?int
    {
        return self::estaAutenticado() ? (int) $_SESSION[self::CHAVE_USUARIO]['id'] : null;
    }

    public static function nomeUsuario(): ?string
    {
        return self::estaAutenticado() ? (string) $_SESSION[self::CHAVE_USUARIO]['nome'] : null;
    }

    /** Encerra a sessão: limpa os dados, apaga o cookie e destrói a sessão no servidor. */
    public static function encerrar(): void
    {
        self::iniciar();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?: 'Lax',
            ]);
        }

        session_destroy();
    }

    // ---------------------------------------------------------------
    // Mensagens flash
    // ---------------------------------------------------------------

    /** @param string $tipo "sucesso" ou "erro" */
    public static function definirFlash(string $tipo, string $mensagem): void
    {
        self::iniciar();
        $_SESSION[self::CHAVE_FLASH] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    /** Devolve a mensagem flash e a remove (só aparece uma vez). */
    public static function obterFlash(): ?array
    {
        self::iniciar();
        $flash = $_SESSION[self::CHAVE_FLASH] ?? null;
        unset($_SESSION[self::CHAVE_FLASH]);
        return $flash;
    }

    // ---------------------------------------------------------------
    // CSRF
    // ---------------------------------------------------------------

    public static function tokenCsrf(): string
    {
        self::iniciar();
        if (empty($_SESSION[self::CHAVE_CSRF])) {
            $_SESSION[self::CHAVE_CSRF] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CHAVE_CSRF];
    }

    public static function validarCsrf(?string $token): bool
    {
        self::iniciar();
        $esperado = $_SESSION[self::CHAVE_CSRF] ?? '';
        return $token !== null && $esperado !== '' && hash_equals($esperado, $token);
    }

    private static function conexaoSegura(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
