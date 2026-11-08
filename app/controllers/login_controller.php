<?php
include_once("../models/user.php");

session_start();

header('Content-Type: application/json; charset=utf-8');

class LoginControle {

    public function logar() {
        $conn = Conexao::conectar();

        try {
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            // Buscar usuário
            $query = "SELECT id, nome, senha FROM usuario WHERE email = :email";
            $stmt = Conexao::executarComParametros($query, [':email' => $email]);
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                throw new Exception("Usuário não encontrado");
            }

            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                throw new Exception("Senha incorreta");
            }

            $userId = $usuario['id'];
            $tipo_usuario = null;

            // Orientador
            $stmt = Conexao::executarComParametros(
                "SELECT id FROM orientador WHERE id = :id",
                [':id' => $userId]
            );
            if ($stmt->fetch()) $tipo_usuario = 'orientador';

            // Administrador
            if (!$tipo_usuario) {
                $stmt = Conexao::executarComParametros(
                    "SELECT id FROM administrador WHERE id = :id",
                    [':id' => $userId]
                );
                if ($stmt->fetch()) $tipo_usuario = 'administrador';
            }

            // Coordenador
            if (!$tipo_usuario) {
                $stmt = Conexao::executarComParametros(
                    "SELECT id FROM coordenador WHERE id = :id",
                    [':id' => $userId]
                );
                if ($stmt->fetch()) $tipo_usuario = 'coordenador';
            }

            // Aluno
            if (!$tipo_usuario) {
                $stmt = Conexao::executarComParametros(
                    "SELECT id FROM aluno WHERE id = :id",
                    [':id' => $userId]
                );
                if ($stmt->fetch()) $tipo_usuario = 'aluno';
            }

            if (!$tipo_usuario) {
                throw new Exception("Tipo de usuário não identificado");
            }

            $_SESSION['usuario_id'] = $userId;
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $tipo_usuario;

            // (opcional) segurança extra
            session_regenerate_id(true);

            echo json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'usuario_id' => $userId,
                'usuario_nome' => $usuario['nome'],
                'usuario_tipo' => $tipo_usuario
            ]);

        } catch (Exception $e) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // LOGOUT
    public function logout() {
        session_start();
        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => 'Logout realizado'
        ]);
    }
}

$controle = new LoginControle();
$acao = $_POST["acao"] ?? null;

if ($acao == "logar") {
    $controle->logar();
}

if ($acao == "logout") {
    $controle->logout();
}
?>