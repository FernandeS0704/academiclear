<?php

include_once("../config/conexao.php");

class Aluno {

    public $id;
    public $matricula;
    public $curso;

    public function inserir() {

        try {

            $parametros = Array(
                ':id' => $this->id,
                ':matricula' => $this->matricula,
                ':curso' => $this->curso,
            );

            $query = "INSERT INTO aluno
                    (id, matricula, curso)
                    VALUES
                    (:id, :matricula, :curso)";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao inserir aluno: " . $e->getMessage());
        }
    }

    public static function contar() {
        $query = "SELECT COUNT(*) AS total FROM aluno";

        $stmt = Conexao::executar($query);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $resultado['total'];
    }
    
    public static function buscarAlunosCoordenador() {
        $query = "
            SELECT 
                usuario.nome,
                usuario.email,
                projeto.titulo AS titulo_projeto,
                projeto.grupo_id
            FROM aluno
            JOIN usuario 
                ON usuario.id = aluno.id
            LEFT JOIN projeto 
                ON projeto.grupo_id = aluno.grupo_id";

        $stmt = Conexao::executar($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarAluno($usuario_id) {
        try {
            $query = "SELECT u.nome, u.email, a.id, a.matricula, a.curso, g.nome AS grupo
                    FROM aluno a
                    JOIN usuario u ON a.id = u.id
                    LEFT JOIN grupo g ON g.id = a.grupo_id
                    WHERE a.id = :id";

            $parametros = [':id' => $usuario_id];
            $resultado = Conexao::executarComParametros($query, $parametros);

            if ($resultado) {
                $aluno = $resultado->fetch(PDO::FETCH_ASSOC);
                if (!$aluno) {
                    throw new Exception("Aluno não encontrado");
                }
                if (empty($aluno['grupo'])) {
                    $aluno['grupo'] = 'Sem grupo';
                }
                return $aluno;
            }

            throw new Exception("Aluno não encontrado");

        } catch (Exception $e) {
            throw new Exception("Erro ao buscar aluno: " . $e->getMessage());
        }
    }

    public function editarAluno() {
        try {
            $parametros = [
                ':id' => $this->id,
                ':matricula' => $this->matricula,
                ':curso' => $this->curso,
            ];

            $query = "UPDATE aluno
                    SET matricula = :matricula, curso = :curso
                    WHERE id = :id";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao editar aluno: " . $e->getMessage());
        }
    }
}
?>