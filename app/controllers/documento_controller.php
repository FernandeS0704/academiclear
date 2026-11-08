<?php
session_start();
include_once("../models/documento.php");
include_once("../config/conexao.php");

header('Content-Type: application/json; charset=utf-8');

class DocumentoControle {

    private function validarAluno() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'aluno') {
            throw new Exception("Acesso não autorizado");
        }
    }

    private function obterProjetoDoAluno() {
        $stmt = Conexao::executarComParametros(
            "SELECT p.id
             FROM projeto p
             INNER JOIN aluno a ON a.grupo_id = p.grupo_id
             WHERE a.id = :aluno_id
             LIMIT 1",
            [':aluno_id' => $_SESSION['usuario_id']]
        );

        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$projeto) {
            throw new Exception("Você precisa ter um projeto cadastrado antes de enviar documentos");
        }

        return (int) $projeto['id'];
    }

    private function validarArquivo($arquivo) {
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Falha no envio do arquivo");
        }

        $extensoesPermitidas = ['pdf', 'doc', 'docx'];
        $nomeOriginal = $arquivo['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new Exception("Tipo de arquivo não permitido. Envie apenas PDF ou DOC");
        }

        $mimesPermitidos = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream',
            ],
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $mimesPermitidos[$extensao], true)) {
            throw new Exception("O conteúdo do arquivo não corresponde a um PDF ou DOC válido");
        }

        $tamanhoMax = 10 * 1024 * 1024; // 10 MB
        if ($arquivo['size'] > $tamanhoMax) {
            throw new Exception("Arquivo muito grande. Tamanho máximo: 10 MB");
        }

        return $extensao;
    }

    public function buscar() {
        try {
            $this->validarAluno();
            $projeto_id = $this->obterProjetoDoAluno();
            $documentos = Documento::buscarPorProjeto($projeto_id);

            echo json_encode([
                'success' => true,
                'documentos' => $documentos,
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function upload() {
        try {
            $this->validarAluno();
            $projeto_id = $this->obterProjetoDoAluno();

            if (!isset($_FILES['arquivo'])) {
                throw new Exception("Nenhum arquivo foi enviado");
            }

            $extensao = $this->validarArquivo($_FILES['arquivo']);

            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') {
                throw new Exception("Informe o título do documento");
            }

            $versao = Documento::buscarProximaVersao($projeto_id, $titulo);

            $pasta = __DIR__ . "/../../public/uploads/documentos/projeto_{$projeto_id}";
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $titulo) . "_v{$versao}.{$extensao}";
            $caminhoCompleto = $pasta . "/" . $nomeArquivo;

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
                throw new Exception("Não foi possível salvar o arquivo no servidor");
            }

            $pathRelativo = "public/uploads/documentos/projeto_{$projeto_id}/{$nomeArquivo}";

            $documento = new Documento();
            $documento->titulo = $titulo;
            $documento->path = $pathRelativo;
            $documento->versao = $versao;
            $documento->projeto_id = $projeto_id;
            $documento->inserir();

            echo json_encode([
                'success' => true,
                'message' => 'Documento enviado com sucesso',
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

$controle = new DocumentoControle();
$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;

if ($acao === 'buscar') {
    $controle->buscar();
} else if ($acao === 'upload') {
    $controle->upload();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Ação inválida',
    ]);
}
?>
