<?php
declare(strict_types=1);

/**
 * Controller de autenticação: cadastro, login, logout e proteção de páginas.
 *
 * Recebe os dados do formulário, delega as regras ao AutenticacaoService,
 * controla a sessão (Sessao) e decide qual view exibir ou para onde redirecionar.
 */
class AutenticacaoController extends ControllerBase
{
    private ?AutenticacaoService $servico = null;

    // ---------------------------------------------------------------
    // Cadastro
    // ---------------------------------------------------------------

    public function exibirCadastro(): void
    {
        $this->redirecionarSeAutenticado();
        $this->renderizarCadastro();
    }

    public function cadastrar(): void
    {
        $this->redirecionarSeAutenticado();

        if (!$this->ehPost()) {
            self::redirecionar('cadastro.php');
        }

        // Nunca devolvemos as senhas ao formulário, apenas nome e e-mail.
        $antigo = ['nome' => $this->entrada('nome'), 'email' => $this->entrada('email')];

        if (!$this->tokenCsrfValido()) {
            http_response_code(403);
            $this->renderizarCadastro([], 'Sua sessão expirou. Tente novamente.', $antigo);
            return;
        }

        try {
            $this->servico()->cadastrar(
                $this->entrada('nome'),
                $this->entrada('email'),
                $this->entrada('senha'),
                $this->entrada('confirmacao')
            );
        } catch (ValidacaoException $e) {
            http_response_code(422);
            $this->renderizarCadastro($e->getErros(), null, $antigo);
            return;
        } catch (RuntimeException $e) {
            error_log('Falha no cadastro: ' . $e->getMessage());
            http_response_code(500);
            $this->renderizarCadastro([], 'Não foi possível concluir o cadastro agora. Tente novamente em instantes.', $antigo);
            return;
        }

        Sessao::definirFlash('sucesso', 'Cadastro realizado com sucesso! Faça login para continuar.');
        self::redirecionar('login.php');
    }

    // ---------------------------------------------------------------
    // Login
    // ---------------------------------------------------------------

    public function exibirLogin(): void
    {
        $this->redirecionarSeAutenticado();
        $this->renderizarLogin();
    }

    public function autenticar(): void
    {
        $this->redirecionarSeAutenticado();

        if (!$this->ehPost()) {
            self::redirecionar('login.php');
        }

        $antigo = ['email' => $this->entrada('email')];

        if (!$this->tokenCsrfValido()) {
            http_response_code(403);
            $this->renderizarLogin('Sua sessão expirou. Tente novamente.', $antigo);
            return;
        }

        try {
            $usuario = $this->servico()->autenticar($this->entrada('email'), $this->entrada('senha'));
        } catch (RuntimeException $e) {
            error_log('Falha no login: ' . $e->getMessage());
            http_response_code(500);
            $this->renderizarLogin('Não foi possível entrar agora. Tente novamente em instantes.', $antigo);
            return;
        }

        if ($usuario === null) {
            http_response_code(401);
            $this->renderizarLogin('E-mail ou senha incorretos.', $antigo);
            return;
        }

        Sessao::autenticar($usuario); // regenera o ID da sessão
        self::redirecionar('dashboard.php');
    }

    // ---------------------------------------------------------------
    // Logout
    // ---------------------------------------------------------------

    /** O logout só é aceito por POST com token CSRF válido, para ninguém deslogar o usuário por link. */
    public function sair(): void
    {
        $destinoPadrao = Sessao::estaAutenticado() ? 'dashboard.php' : 'login.php';

        if (!$this->ehPost() || !$this->tokenCsrfValido()) {
            self::redirecionar($destinoPadrao);
        }

        Sessao::encerrar();
        Sessao::iniciar(); // nova sessão (vazia) apenas para exibir a mensagem
        Sessao::definirFlash('sucesso', 'Você saiu da sua conta.');
        self::redirecionar('login.php');
    }

    // ---------------------------------------------------------------
    // Controle de acesso
    // ---------------------------------------------------------------

    /**
     * Protege páginas internas. Chame no início de toda página que exige login.
     * Redireciona para o login se não houver usuário autenticado.
     *
     * @return int o id do usuário autenticado
     */
    public static function exigirAutenticacao(): int
    {
        // Impede que o navegador guarde a página: após o logout, o botão "voltar" não a exibe.
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if (!Sessao::estaAutenticado()) {
            Sessao::definirFlash('erro', 'Faça login para acessar essa página.');
            self::redirecionar('login.php');
        }

        return (int) Sessao::idUsuario();
    }

    /** Usuário já logado não precisa ver login/cadastro de novo. */
    private function redirecionarSeAutenticado(): void
    {
        if (Sessao::estaAutenticado()) {
            self::redirecionar('dashboard.php');
        }
    }

    // ---------------------------------------------------------------
    // Apoio
    // ---------------------------------------------------------------

    private function servico(): AutenticacaoService
    {
        // Criado só quando necessário: a tela de login abre mesmo com o banco desligado.
        return $this->servico ??= new AutenticacaoService();
    }

    /**
     * @param array<string, string> $erros erros por campo
     * @param array<string, string> $antigo valores a repor no formulário
     */
    private function renderizarCadastro(array $erros = [], ?string $erroGeral = null, array $antigo = []): void
    {
        Visao::renderizar('login/cadastro', [
            'erros'     => $erros,
            'erroGeral' => $erroGeral,
            'antigo'    => $antigo,
            'flash'     => Sessao::obterFlash(),
            'csrf'      => Sessao::tokenCsrf(),
        ]);
    }

    /** @param array<string, string> $antigo */
    private function renderizarLogin(?string $erroGeral = null, array $antigo = []): void
    {
        Visao::renderizar('login/login', [
            'erroGeral' => $erroGeral,
            'antigo'    => $antigo,
            'flash'     => Sessao::obterFlash(),
            'csrf'      => Sessao::tokenCsrf(),
        ]);
    }
}
