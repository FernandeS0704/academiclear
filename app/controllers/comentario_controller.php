<?php
session_start();

include_once("../models/comentario.php");
include_once("../config/conexao.php");

header('Content-Type: application/json; charset=utf-8');

class ComentarioControle {

    public function adicionar() {

        $conn = Conexao::conectar();

        try {
            $texto = trim($_POST['texto'] ?? '');
            $documentoId = $_POST['documento_id'] ?? null;

            $autorId = $_SESSION['usuario_id'] ?? null;

            if (!$texto) {
                throw new Exception("Comentario vazio");
            }

            if (!$documentoId) {
                throw new Exception("Documento invalido");
            }

            if (!$autorId) {
                throw new Exception("Usuario nao autenticado");
            }

            $comentario = new Comentario();

            $comentario->setTexto($texto);
            $comentario->setAutorId($autorId);
            $comentario->setDocumentoId($documentoId);

            $queryVerifica = "
                SELECT id
                FROM comentario
                WHERE autor_id = :autor_id
                AND documento_id = :documento_id
                LIMIT 1
            ";
            
            $stmtVerifica = $conn->prepare($queryVerifica);
            
            $stmtVerifica->execute([
                ":autor_id" => $autorId,
                ":documento_id" => $documentoId
            ]);
            
            $comentarioExistente = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
            
            if ($comentarioExistente) {
                throw new Exception(
                    "Voce ja comentou neste documento"
                );
            }

            $query = "
                INSERT INTO comentario (
                    texto,
                    data_criacao,
                    autor_id,
                    documento_id
                )
                VALUES (
                    :texto,
                    NOW(),
                    :autor_id,
                    :documento_id
                )
            ";

            $stmt = $conn->prepare($query);

            $stmt->execute([
                ":texto" => $comentario->getTexto(),
                ":autor_id" => $comentario->getAutorId(),
                ":documento_id" => $comentario->getDocumentoId()
            ]);

            echo json_encode([
                "success" => true,
                "message" => "Comentario adicionado"
            ]);

        } catch (Exception $e) {

            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function editar() {
        $conn = Conexao::conectar();

        try {

            $comentarioId = $_POST['comentario_id'] ?? null;
            $texto = trim($_POST['texto'] ?? '');

            if (!$comentarioId) {
                throw new Exception("Comentario invalido");
            }

            if (!$texto) {
                throw new Exception("Texto vazio");
            }

            $query = "
                UPDATE comentario
                SET texto = :texto
                WHERE id = :id
            ";

            $stmt = $conn->prepare($query);

            $stmt->execute([
                ":texto" => $texto,
                ":id" => $comentarioId
            ]);

            echo json_encode([
                "success" => true
            ]);

        } catch (Exception $e) {

            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function excluir() {

        $conn = Conexao::conectar();

        try {

            $comentarioId = $_POST['comentario_id'] ?? null;

            if (!$comentarioId) {
                throw new Exception("Comentario invalido");
            }

            $query = "
                DELETE FROM comentario
                WHERE id = :id
            ";

            $stmt = $conn->prepare($query);

            $stmt->execute([
                ":id" => $comentarioId
            ]);

            echo json_encode([
                "success" => true
            ]);

        } catch (Exception $e) {

            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
}


$acao = $_POST['acao'] ?? '';
$controle = new ComentarioControle();

switch ($acao) {

    case
     "adicionarComentario":
        $controle->adicionar();
        break;
    
    case "editarComentario":
        $controle->editar();
        break;

    case "excluirComentario":
        $controle->excluir();
        break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Acao invalida"
        ]);
}