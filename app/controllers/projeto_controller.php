<?php
include_once("../models/projeto.php");
include_once("../config/conexao.php");

session_start();

header('Content-Type: application/json; charset=utf-8');

class ProjetoControle {

    public function criar() {
        $conn = Conexao::conectar();

        try {
            $conn->beginTransaction();

            // Obter ID do aluno logado da session
            $aluno_id = $_SESSION['usuario_id'] ?? null;
            if (!$aluno_id) {
                throw new Exception("Aluno não autenticado");
            }

            // Buscar grupo_id do aluno
            $stmt = Conexao::executarComParametros(
                "SELECT grupo_id FROM aluno WHERE id = :id",
                [':id' => $aluno_id]
            );
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$aluno) {
                throw new Exception("Aluno não encontrado");
            }
            
            $grupo_id = $aluno['grupo_id'];
            if (!$grupo_id) {
                throw new Exception("Aluno não possui um grupo atribuído");
            }

            // Criar projeto com o grupo_id do aluno
            $projeto = new Projeto();
            $projeto->titulo = $_POST['titulo'];
            $projeto->descricao = $_POST['descricao'];
            $projeto->objetivo = $_POST['objetivo'];
            $projeto->temas = $_POST['temas'];
            $projeto->areas = $_POST['areas'];
            $projeto->grupo_id = $grupo_id;

            $projeto->inserir();

            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Projeto criado com sucesso'
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar projeto',
                'error'   => $e->getMessage()
            ]);
        }
    }

    //se não for aluno, for orientador, tem que pegar o próprio ID do orientador logado, e buscar todos os projetos que possuem esse id

    public function buscar() {
        try {
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $usuario_tipo = $_SESSION['usuario_tipo'] ?? null;

            if (!$usuario_id) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ]);
                return;
            }

            if ($usuario_tipo === 'orientador') {
                $projeto = new Projeto();
                $projeto->orientador_id = $usuario_id;
                $projetos = $projeto->buscarPorOrientador();

                echo json_encode([
                    'success' => true,
                    'message' => empty($projetos) ? 'Orientador sem projetos associados' : null,
                    'data' => $projetos
                ]);
                return;
            }

            if ($usuario_tipo !== 'aluno') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Tipo de usuário não autorizado'
                ]);
                return;
            }

            $stmt = Conexao::executarComParametros(
                "SELECT grupo_id FROM aluno WHERE id = :id",
                [':id' => $usuario_id]
            );
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$aluno || !$aluno['grupo_id']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Aluno sem grupo associado',
                    'data' => null,
                    'tem_grupo' => false
                ]);
                return;
            }

            $projeto = new Projeto();
            $projeto->grupo_id = $aluno['grupo_id'];
            $projeto = $projeto->buscarPorGrupo();

            echo json_encode([
                'success' => true,
                'data' => $projeto ?: null,
                'tem_grupo' => true
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar projeto',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function buscarProjetoPorGrupo() {

        try {

            $grupo_id = $_POST['grupo_id'] ?? $_GET['grupo_id'] ?? null;

            if (!$grupo_id) {

                throw new Exception("Grupo não informado");
            }

            $projeto = new Projeto();

            $projeto->grupo_id = $grupo_id;

            $dadosProjeto = $projeto->buscarPorGrupo();

            echo json_encode([
                'success' => true,
                'data' => $dadosProjeto
            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar projeto',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function buscarProjetoPorOrientador() {

        try {

            $orientador_id = $_POST['orientador_id'] ?? null;

            if (!$orientador_id) {

                throw new Exception("Orientador não informado");
            }

            $projeto = new Projeto();

            $projeto->orientador_id = $orientador_id;

            $projetos = $projeto->buscarPorOrientador();

            echo json_encode([
                'success' => true,
                'projetos' => $projetos
            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar projeto',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function vincularProjeto(){
        try {
            $projeto_id = $_POST['projeto_id'] ?? null;
            $orientador_id = $_POST['orientador_id'] ?? null;

            if (!$projeto_id || !$orientador_id) {
                throw new Exception("Projeto ou Orientador não informado");
            }

            $projeto = new Projeto();
            $projeto->id = $projeto_id;
            $projeto->orientador_id = $orientador_id;

            $projeto->vincularOrientador();

            echo json_encode([
                'success' => true,
                'message' => 'Projeto vinculado ao orientador com sucesso'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao vincular projeto',
                'error' => $e->getMessage()
            ]);
        }
    }
}

$controle = new ProjetoControle();

$acao = $_POST["acao"] ?? $_GET["acao"] ?? null;

if ($acao == "criar") {
    $controle->criar();
} else if ($acao == "buscar") {
    $controle->buscar();
} else if ($acao == "buscarProjetoPorGrupo") {
    $controle->buscarProjetoPorGrupo();
} else if ($acao == "buscarProjetoPorOrientador") {
    $controle->buscarProjetoPorOrientador();
} else if ($acao == "vincularProjeto") {
    $controle->vincularProjeto();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ação inválida'
    ]);
}   
?>