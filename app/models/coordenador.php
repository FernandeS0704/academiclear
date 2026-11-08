<?php

include_once("../config/conexao.php");

class Coordenador {

    public $id;
    public $departamento;
    public $instituicao;

    public function inserir() {

        try {

            $parametros = Array(
                ':id' => $this->id,
                ':departamento' => $this->departamento,
                ':instituicao' => $this->instituicao,
            );

            $query = "INSERT INTO coordenador
                    (id, departamento, instituicao_id)
                    VALUES
                    (:id, :departamento, :instituicao)";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao inserir coordenador: " . $e->getMessage());
        }
    }

    public function buscarCoordenador($usuario_id) {
        try {
            $query = "SELECT u.nome, u.email, c.id, c.departamento, i.nome AS instituicao, i.id AS instituicao_id
                    FROM coordenador c
                    JOIN instituicao i ON c.instituicao_id = i.id
                    JOIN usuario u ON c.id = u.id
                    WHERE c.id = :id";

            $parametros = Array(':id' => $usuario_id);

            $resultado = Conexao::executarComParametros($query, $parametros);

            if ($resultado) {
                return $resultado->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Coordenador não encontrado");
            }

        } catch (Exception $e) {
            throw new Exception("Erro ao buscar coordenador: " . $e->getMessage());
        }
    }

    public function editarCoordenador() {
        try {
            $parametros = Array(
                ':id' => $this->id,
                ':departamento' => $this->departamento,
                ':instituicao_id' => $this->instituicao
            );

            $query = "UPDATE coordenador
                    SET departamento = :departamento, instituicao_id = :instituicao_id
                    WHERE id = :id";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao editar coordenador: " . $e->getMessage());
        }
    }
}
?>