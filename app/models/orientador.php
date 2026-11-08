<?php

include_once("../config/conexao.php");

class Orientador {

    public $id;
    public $atuacao;
    public $titulacao;

    public function inserir() {

        try {

            $parametros = Array(
                ':id' => $this->id,
                ':atuacao' => $this->atuacao,
                ':titulacao' => $this->titulacao,
            );

            $query = "INSERT INTO orientador
                    (id, areaAtuacao, titulacao)
                    VALUES
                    (:id, :atuacao, :titulacao)";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao inserir orientador: " . $e->getMessage());
        }
    }

    public function editarOrientador() {
        $conn = Conexao::conectar();

        $sql = "
            UPDATE orientador
            SET
                areaAtuacao = :areaAtuacao,
                titulacao = :titulacao
            WHERE id = :id
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":areaAtuacao", $this->areaAtuacao);
        $stmt->bindValue(":titulacao", $this->titulacao);
        $stmt->bindValue(":id", $this->id);

        return $stmt->execute();
    }

    public static function contar() {
        $query = "SELECT COUNT(*) AS total FROM orientador";

        $stmt = Conexao::executar($query);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $resultado['total'];
    }

    public static function buscarOrientador() {
        $query = "
            SELECT 
                orientador.id,
                usuario.nome
            FROM orientador
            INNER JOIN usuario 
                ON orientador.id = usuario.id
        ";

        $stmt = Conexao::executar($query);

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public static function buscarOrientadoresCoordenador() {
        $query = "
            SELECT 
                usuario.id,
                usuario.nome,
                usuario.email,
                projeto.grupo_id
            FROM orientador
            JOIN usuario 
                ON usuario.id = orientador.id
            JOIN projeto 
                ON projeto.orientador_id = orientador.id";

        $stmt = Conexao::executar($query);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>