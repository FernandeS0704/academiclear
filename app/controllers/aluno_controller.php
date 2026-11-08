<?php
session_start();
include_once("../models/user.php");
include_once("../models/aluno.php");
header('Content-Type: application/json; charset=utf-8');

class AlunoControle {

    private function validarAlunoLogado($usuario_id) {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
            throw new Exception("Acesso não autorizado");
        }
        if ((int) $usuario_id !== (int) $_SESSION['usuario_id']) {
            throw new Exception("Você só pode acessar seu próprio perfil");
        }
    }

    public function cadastrar() {
        $conn = Conexao::conectar();

        try {
            $conn->beginTransaction();

            $user = new User();
            $user->nome = $_POST['nome'];
            $user->email = $_POST['email'];
            $user->senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $user->dataCadastro = $_POST['data_cadastro'];

            $userId = $user->inserir();

            $aluno = new Aluno();
            $aluno->id = $userId;
            $aluno->matricula = $_POST['matricula'];
            $aluno->curso = $_POST['curso'];

            $aluno->inserir();

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Aluno cadastrado com sucesso',
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao cadastrar usuário',
                'error'   => $e->getMessage() // em produção você pode ocultar
            ]);
        }
    }

    public function buscarAluno() {
        try {
            $this->validarAlunoLogado($_POST['usuario_id']);

            $aluno = new Aluno();
            $dados = $aluno->buscarAluno($_POST['usuario_id']);

            echo json_encode([
                'success' => true,
                'aluno' => $dados
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar dados do aluno',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function editarAluno() {
        try {
            $this->validarAlunoLogado($_POST['usuario_id']);

            $aluno = new Aluno();
            $aluno->id = $_POST['usuario_id'];
            $aluno->matricula = $_POST['matricula'];
            $aluno->curso = $_POST['curso'];

            $user = new User();
            $user->id = $_POST['usuario_id'];
            $user->nome = $_POST['nome'];
            $user->email = $_POST['email'];

            $aluno->editarAluno();
            $user->editarUser();

            $_SESSION['usuario_nome'] = $user->nome;

            echo json_encode([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao editar perfil',
                'error' => $e->getMessage()
            ]);
        }
    }
}

$controle = new AlunoControle();
$acao = $_POST["acao"] ?? null;

if ($acao == "cadastrar") {
    $controle->cadastrar();
} else if ($acao == "buscarAluno") {
    $controle->buscarAluno();
} else if ($acao == "editarAluno") {
    $controle->editarAluno();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ação inválida'
    ]);
}
?>