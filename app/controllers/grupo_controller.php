<?php
session_start();
include_once("../models/grupo.php");
include_once("../models/projeto.php");
header('Content-Type: application/json; charset=utf-8');

class GrupoControle {

    private function validarAlunoLogado() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
            throw new Exception("Acesso não autorizado");
        }
    }

    public function adicionar() {
        $email = $_POST['email'];

        $parametros = [
            ":email" => $email
        ];

        $query = "SELECT aluno.id 
              FROM aluno
              JOIN usuario ON aluno.id = usuario.id
              WHERE usuario.email = :email";

        $resultado = Conexao::executarComParametros($query, $parametros)->fetch();
        
        if ($resultado) {
            $alunoId = $resultado['id'];

            echo json_encode([
                'success' => true,
                'message' => 'Aluno encontrado',
                'aluno_id' => $alunoId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email não encontrado'
            ]);
        }
    }
    

    public function cadastrar() {
        $conn = Conexao::conectar();

        try {
            $alunoLogadoId = $_SESSION['usuario_id'] ?? null;
            if (!$alunoLogadoId) {
                throw new Exception("Usuário não autenticado");
            }

            $conn->beginTransaction();

            $grupo = new Grupo();
            $grupo->nome = $_POST['nome'];
            $grupo->descricao = $_POST['descricao'];

            $grupoId = $grupo->inserir();

            $participantes = json_decode($_POST['participantes'], true) ?: [];

            // Quem cria o grupo também entra como participante
            if (!in_array((int) $alunoLogadoId, array_map('intval', $participantes), true)) {
                $participantes[] = (int) $alunoLogadoId;
            }

            foreach ($participantes as $alunoId) {

                $query = "UPDATE aluno SET grupo_id = :grupo_id WHERE id = :id";

                $parametros = [
                    ':grupo_id' => $grupoId,
                    ':id' => $alunoId
                ];

                Conexao::executarComParametros($query, $parametros);
            }

            
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Grupo criado com sucesso',
            ]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar grupo',
                'error'   => $e->getMessage() // em produção você pode ocultar
            ]);
        }
    }

    public function buscarGrupos() {
        $orientadorId = $_SESSION['usuario_id'];

        $query = "
            SELECT 
                g.id,
                g.nome,
                g.descricao,
                g.dataCriacao,

                GROUP_CONCAT(
                    DISTINCT u.nome SEPARATOR ', '
                ) AS integrantes

            FROM grupo g

            INNER JOIN projeto p
                ON p.grupo_id = g.id

            LEFT JOIN aluno a
                ON a.grupo_id = g.id

            LEFT JOIN usuario u
                ON u.id = a.id

            WHERE p.orientador_id = :orientador_id

            GROUP BY g.id
        ";

        $parametros = [
            ':orientador_id' => $orientadorId
        ];

        $resultado = Conexao::executarComParametros($query, $parametros)
            ->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'grupos' => $resultado
        ]);
    }

    public function buscarMeuGrupo() {
        try {
            $this->validarAlunoLogado();

            $stmt = Conexao::executarComParametros(
                "SELECT a.grupo_id, g.nome AS grupo_nome
                 FROM aluno a
                 LEFT JOIN grupo g ON g.id = a.grupo_id
                 WHERE a.id = :id",
                [':id' => $_SESSION['usuario_id']]
            );

            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$aluno || !$aluno['grupo_id']) {
                echo json_encode([
                    'success' => true,
                    'tem_grupo' => false,
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'tem_grupo' => true,
                'grupo_id' => $aluno['grupo_id'],
                'grupo_nome' => $aluno['grupo_nome'],
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sairGrupo() {
        try {
            $this->validarAlunoLogado();

            $stmt = Conexao::executarComParametros(
                "SELECT grupo_id FROM aluno WHERE id = :id",
                [':id' => $_SESSION['usuario_id']]
            );

            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$aluno || !$aluno['grupo_id']) {
                throw new Exception("Você não está em nenhum grupo");
            }

            Conexao::executarComParametros(
                "UPDATE aluno SET grupo_id = NULL WHERE id = :id",
                [':id' => $_SESSION['usuario_id']]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Você saiu do grupo com sucesso',
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}


$controle = new GrupoControle();
$acao = $_POST["acao"] ?? null;

if ($acao == "cadastrar") {
    $controle->cadastrar();

} else if ($acao == "adicionar") {
    $controle->adicionar();

} else if ($acao == "buscarGrupos") {
    $controle->buscarGrupos();

} else if ($acao == "buscarMeuGrupo") {
    $controle->buscarMeuGrupo();

} else if ($acao == "sairGrupo") {
    $controle->sairGrupo();

} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ação inválida',
    ]);
}
?>