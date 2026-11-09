<?php
declare(strict_types=1);

/**
 * Controller do Dashboard (página inicial protegida).
 *
 * Lê os filtros da URL (busca, disciplina, status, prioridade e ordenação),
 * pede os dados ao DashboardService e escolhe a view. Os filtros usam GET,
 * então a página filtrada pode ser recarregada e compartilhada pelo endereço.
 */
class DashboardController extends ControllerBase
{
    public function exibir(): void
    {
        $idUsuario = AutenticacaoController::exigirAutenticacao();

        try {
            $usuario = (new UsuarioRepository())->buscarPorId($idUsuario);
            $painel = $usuario === null ? [] : (new DashboardService())->montar($idUsuario, $_GET);
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
            'flash'   => Sessao::obterFlash(),
        ] + $painel);
    }
}
