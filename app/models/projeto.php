<?php

include_once("../config/conexao.php");

class Projeto {

    public $id;
    public $titulo;
    public $descricao;
    public $objetivo;
    public $temas;
    public $areas;
    public $orientador_id;
    public $grupo_id;
    public $orientador_nome;
    public $documentos;

    private function carregarDocumentosPorProjeto($projetoId) {
        $docsStmt = Conexao::executarComParametros(
            "SELECT id, titulo, versao, dataCriacao, path
             FROM documento
             WHERE projeto_id = :projeto_id
             ORDER BY titulo, versao DESC, id DESC",
            [':projeto_id' => $projetoId]
        );

        $docs = [];
        $docIds = [];
        while ($doc = $docsStmt->fetch(PDO::FETCH_ASSOC)) {
            $docs[] = $doc;
            $docIds[] = $doc['id'];
        }

        $comentariosPorDocumento = [];
        if (!empty($docIds)) {
            $placeholders = [];
            $params = [];

            foreach ($docIds as $index => $docId) {
                $placeholder = ':doc' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $docId;
            }

            $comentariosStmt = Conexao::executarComParametros(
                "SELECT 
                    c.id,
                    c.texto,
                    c.data_criacao,
                    c.documento_id,
                    c.autor_id,
                    u.nome AS autor_nome
                 FROM comentario c
                 LEFT JOIN usuario u ON u.id = c.autor_id
                 WHERE c.documento_id IN (" . implode(',', $placeholders) . ")
                 ORDER BY c.data_criacao DESC, c.id DESC",
                $params
            );

            while ($comentario = $comentariosStmt->fetch(PDO::FETCH_ASSOC)) {
                $docId = $comentario['documento_id'];
                if (!isset($comentariosPorDocumento[$docId])) {
                    $comentariosPorDocumento[$docId] = [];
                }
                $comentariosPorDocumento[$docId][] = $comentario;
            }
        }

        $documentos = [];
        foreach ($docs as $doc) {
            $titulo = $doc['titulo'] ?? 'Sem título';

            if (!isset($documentos[$titulo])) {
                $documentos[$titulo] = [
                    'id' => $doc['id'],
                    'titulo' => $titulo,
                    'versoes' => []
                ];
            }

            $doc['comentarios'] = $comentariosPorDocumento[$doc['id']] ?? [];
            $documentos[$titulo]['versoes'][] = $doc;
        }

        return array_values($documentos);
    }

    public function inserir() {

        try {

            $parametros = Array(
                ':titulo' => $this->titulo,
                ':descricao' => $this->descricao,
                ':objetivo' => $this->objetivo,
                ':temas' => $this->temas,
                ':areas' => $this->areas,
                ':grupo_id' => $this->grupo_id
            );

            $query = "INSERT INTO projeto
                    (titulo, descricao, objetivo, temas, areas, grupo_id)
                    VALUES
                    (:titulo, :descricao, :objetivo, :temas, :areas, :grupo_id)";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao inserir projeto: " . $e->getMessage());
        }
    }

    public function buscarPorGrupo() {

        try {

            $parametros = Array(
                ':grupo_id' => $this->grupo_id
            );

                 $query = "SELECT p.id,
                             p.titulo,
                             p.descricao,
                             p.objetivo,
                             p.temas,
                             p.areas,
                             p.estado,
                             u.nome AS orientador_nome
                      FROM projeto p
                      LEFT JOIN orientador o ON o.id = p.orientador_id
                      LEFT JOIN usuario u ON u.id = o.id
                      WHERE p.grupo_id = :grupo_id
                      LIMIT 1";

            $stmt = Conexao::executarComParametros($query, $parametros);

            $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$projeto) {
                return null;
            }

            $projeto['documentos'] = $this->carregarDocumentosPorProjeto($projeto['id']);

            return $projeto;

        } catch (Exception $e) {
            throw new Exception("Erro ao buscar projeto: " . $e->getMessage());
        }
    }

    public static function contar() {
        $query = "SELECT COUNT(*) AS total FROM projeto";

        $stmt = Conexao::executar($query);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $resultado['total'];
    }

    public static function buscarSemOrientador() {
        $query = "SELECT id, titulo FROM projeto WHERE orientador_id IS NULL";

        $stmt = Conexao::executar($query);

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function buscarPorOrientador() {
        try {
            $parametros = [
                ':orientador_id' => $this->orientador_id
            ];

            $query = "SELECT p.id,
                             p.titulo,
                             p.descricao,
                             p.objetivo,
                             p.temas,
                             p.areas,
                             p.estado,
                             p.grupo_id,
                             u.nome AS orientador_nome
                      FROM projeto p
                      LEFT JOIN orientador o ON o.id = p.orientador_id
                      LEFT JOIN usuario u ON u.id = o.id
                      WHERE p.orientador_id = :orientador_id
                      ORDER BY p.id DESC";

            $stmt = Conexao::executarComParametros($query, $parametros);
            $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($projetos as $index => $projeto) {
                $projetos[$index]['documentos'] = $this->carregarDocumentosPorProjeto($projeto['id']);
            }

            return $projetos;

        } catch (Exception $e) {
            throw new Exception("Erro ao buscar projetos do orientador: " . $e->getMessage());
        }
    }

    public function vincularOrientador() {
        try {
            $parametros = [
                ':projeto_id' => $this->id,
                ':orientador_id' => $this->orientador_id
            ];

            $query = "UPDATE projeto
                      SET orientador_id = :orientador_id
                      WHERE id = :projeto_id";

            Conexao::executarComParametros($query, $parametros);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao vincular orientador: " . $e->getMessage());
        }
    }
}
?>