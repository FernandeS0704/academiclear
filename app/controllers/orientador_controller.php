<?php
include_once("../models/user.php");
include_once("../models/orientador.php");
header('Content-Type: application/json; charset=utf-8');

class OrientadorControle {

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

            $orientador = new Orientador();
            $orientador->id = $userId;
            $orientador->atuacao = $_POST['atuacao'];
            $orientador->titulacao = $_POST['titulacao'];

            $orientador->inserir();

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Orientador cadastrado com sucesso',
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
    public function buscarOrientador() {
        $usuario_id = $_POST['usuario_id'];

        $conn = Conexao::conectar();

        $sql = "
            SELECT
                usuario.nome,
                usuario.email,
                orientador.areaAtuacao,
                orientador.titulacao
            FROM usuario
            INNER JOIN orientador
                ON usuario.id = orientador.id
            WHERE usuario.id = :id
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":id", $usuario_id);

        $stmt->execute();

        $orientador = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($orientador) {

            echo json_encode([
                "success" => true,
                "orientador" => $orientador
            ]);

        } else {

            echo json_encode([
                "success" => false,
                "message" => "Orientador não encontrado"
            ]);
        }
    }
    public function editarOrientador() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $conn = Conexao::conectar();

        try {

            $orientador = new Orientador();

            $orientador->id = $_POST['usuario_id'];

            $orientador->areaAtuacao =
                $_POST['areaAtuacao'];

            $orientador->titulacao =
                $_POST['titulacao'];

            $user = new User();

            $user->id = $_POST['usuario_id'];

            $user->nome = $_POST['nome'];

            $user->email = $_POST['email'];

            $orientador->editarOrientador();

            $user->editarUser();

            if (
                isset($_SESSION['usuario_id']) &&
                $_SESSION['usuario_id'] == $user->id
            ) {

                $_SESSION['usuario_nome'] =
                    $user->nome;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Orientador editado com sucesso'
            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao editar orientador',
                'error' => $e->getMessage()
            ]);
        }
    }
}

$controle = new OrientadorControle();
$acao = $_POST["acao"];

if ($acao == "cadastrar") {
    $controle->cadastrar();
} elseif ($acao == "buscarOrientador") {
    $controle->buscarOrientador();
} elseif ($acao == "editarOrientador") {
    $controle->editarOrientador();
} else {
    http_response_code(400);
}

?>