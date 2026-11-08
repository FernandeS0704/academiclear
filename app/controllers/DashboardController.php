<?php
declare(strict_types=1);

/**
 * Controller do Dashboard.
 * Nesta etapa exibe apenas a página inicial protegida (saudação e botão Sair).
 * O conteúdo real do dashboard será construído nas próximas etapas.
 */
class DashboardController extends ControllerBase
{
    public function exibir(): void
    {
        $idUsuario = AutenticacaoController::exigirAutenticacao();

        try {
            $usuario = (new UsuarioRepository())->buscarPorId($idUsuario);
        } catch (RuntimeException $e) {
            error_log('Falha ao abrir o dashboard: ' . $e->getMessage());
            http_response_code(500);
            Visao::renderizar('compartilhado/erro', [
                'mensagem' => 'Não foi possível carregar seus dados agora. Verifique se o banco de dados está ligado.',
            ]);
            return;
        }

        // Sessão de um usuário que não existe mais (ex.: conta excluída): encerra.
        if ($usuario === null) {
            Sessao::encerrar();
            self::redirecionar('login.php');
        }

        Visao::renderizar('dashboard/index', [
            'usuario' => $usuario,
            'csrf'    => Sessao::tokenCsrf(),
        ]);
    }
}
