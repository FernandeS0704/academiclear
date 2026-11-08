<?php

include_once("../config/conexao.php");

class Documento {

    public $id;
    public $titulo;
    public $path;
    public $versao;
    public $projeto_id;

    public function inserir() {
        try {
            $parametros = [
                ':titulo' => $this->titulo,
                ':path' => $this->path,
                ':versao' => $this->versao,
                ':projeto_id' => $this->projeto_id,
            ];

            $query = "INSERT INTO documento (titulo, path, versao, projeto_id, dataCriacao)
                    VALUES (:titulo, :path, :versao, :projeto_id, NOW())";

            Conexao::executarComParametros($query, $parametros);

            return Conexao::conectar()->lastInsertId();

        } catch (Exception $e) {
            throw new Exception("Erro ao salvar documento: " . $e->getMessage());
        }
    }

    public static function buscarProximaVersao($projeto_id, $titulo) {
        $query = "SELECT COALESCE(MAX(versao), 0) + 1 AS proxima
                  FROM documento
                  WHERE projeto_id = :projeto_id AND titulo = :titulo";

        $stmt = Conexao::executarComParametros($query, [
            ':projeto_id' => $projeto_id,
            ':titulo' => $titulo,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['proxima'];
    }

    public static function buscarPorProjeto($projeto_id) {
        $query = "SELECT id, titulo, path, versao, dataCriacao
                  FROM documento
                  WHERE projeto_id = :projeto_id
                  ORDER BY titulo ASC, versao DESC, id DESC";

        $stmt = Conexao::executarComParametros($query, [
            ':projeto_id' => $projeto_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
