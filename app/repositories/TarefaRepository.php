<?php
declare(strict_types=1);

/**
 * Acesso ao banco para a tabela "tarefa".
 *
 * - Toda operação usa o id_usuario no WHERE: cada usuário só acessa as próprias tarefas.
 * - Ao cadastrar ou atualizar, a disciplina informada deve pertencer ao mesmo usuário.
 * - As listagens devolvem uma ListaEncadeada de Tarefa; construirMapa() devolve
 *   um MapaTarefas (array associativo por ID) para buscas rápidas.
 */
class TarefaRepository extends RepositorioBase
{
    private DisciplinaRepository $disciplinas;

    public function __construct(?PDO $pdo = null, ?DisciplinaRepository $disciplinas = null)
    {
        parent::__construct($pdo);
        $this->disciplinas = $disciplinas ?? new DisciplinaRepository($this->pdo);
    }

    /**
     * Cadastra a tarefa e devolve o mesmo objeto, com ID e data de criação preenchidos.
     *
     * @throws InvalidArgumentException se a disciplina não existir ou não for do usuário
     */
    public function cadastrar(Tarefa $tarefa): Tarefa
    {
        $this->validarDisciplina($tarefa);

        $this->executar(
            'INSERT INTO tarefa (titulo, descricao, prazo, prioridade, status, id_disciplina, id_usuario)
             VALUES (:titulo, :descricao, :prazo, :prioridade, :status, :id_disciplina, :id_usuario)',
            [
                ':titulo'        => $tarefa->getTitulo(),
                ':descricao'     => $tarefa->getDescricao(),
                ':prazo'         => $tarefa->getPrazo(),
                ':prioridade'    => $tarefa->getPrioridade(),
                ':status'        => $tarefa->getStatus(),
                ':id_disciplina' => $tarefa->getIdDisciplina(),
                ':id_usuario'    => $tarefa->getIdUsuario(),
            ]
        );

        $id = (int) $this->pdo->lastInsertId();
        $tarefa->setIdTarefa($id);

        $linha = $this->buscarLinha('SELECT data_criacao FROM tarefa WHERE id_tarefa = :id', [':id' => $id]);
        if ($linha !== null) {
            $tarefa->setDataCriacao($linha['data_criacao']);
        }

        return $tarefa;
    }

    public function buscarPorId(int $idTarefa, int $idUsuario): ?Tarefa
    {
        $linha = $this->buscarLinha(
            'SELECT * FROM tarefa WHERE id_tarefa = :id AND id_usuario = :id_usuario',
            [':id' => $idTarefa, ':id_usuario' => $idUsuario]
        );
        return $linha ? Tarefa::deArray($linha) : null;
    }

    /** Tarefas do usuário, ordenadas por prazo, em uma lista encadeada. */
    public function listarPorUsuario(int $idUsuario): ListaEncadeada
    {
        return $this->montarLista('id_usuario = :id_usuario', [':id_usuario' => $idUsuario]);
    }

    /** Tarefas de uma disciplina do usuário, ordenadas por prazo, em uma lista encadeada. */
    public function listarPorDisciplina(int $idDisciplina, int $idUsuario): ListaEncadeada
    {
        return $this->montarLista(
            'id_disciplina = :id_disciplina AND id_usuario = :id_usuario',
            [':id_disciplina' => $idDisciplina, ':id_usuario' => $idUsuario]
        );
    }

    /** Atalho: tarefas do usuário já indexadas por ID (HashMap). */
    public function construirMapa(int $idUsuario): MapaTarefas
    {
        return MapaTarefas::deColecao($this->listarPorUsuario($idUsuario));
    }

    /**
     * @return bool false se a tarefa não existir ou não for do usuário
     * @throws InvalidArgumentException se a disciplina não existir ou não for do usuário
     */
    public function atualizar(Tarefa $tarefa): bool
    {
        $id = $tarefa->getIdTarefa();
        $idUsuario = $tarefa->getIdUsuario();

        if ($id === null || $this->buscarPorId($id, $idUsuario) === null) {
            return false;
        }

        $this->validarDisciplina($tarefa);

        $this->executar(
            'UPDATE tarefa
             SET titulo = :titulo, descricao = :descricao, prazo = :prazo,
                 prioridade = :prioridade, status = :status, id_disciplina = :id_disciplina
             WHERE id_tarefa = :id AND id_usuario = :id_usuario',
            [
                ':titulo'        => $tarefa->getTitulo(),
                ':descricao'     => $tarefa->getDescricao(),
                ':prazo'         => $tarefa->getPrazo(),
                ':prioridade'    => $tarefa->getPrioridade(),
                ':status'        => $tarefa->getStatus(),
                ':id_disciplina' => $tarefa->getIdDisciplina(),
                ':id'            => $id,
                ':id_usuario'    => $idUsuario,
            ]
        );

        return true;
    }

    public function excluir(int $idTarefa, int $idUsuario): bool
    {
        $stmt = $this->executar(
            'DELETE FROM tarefa WHERE id_tarefa = :id AND id_usuario = :id_usuario',
            [':id' => $idTarefa, ':id_usuario' => $idUsuario]
        );
        return $stmt->rowCount() > 0;
    }

    private function validarDisciplina(Tarefa $tarefa): void
    {
        if (!$this->disciplinas->pertenceAoUsuario($tarefa->getIdDisciplina(), $tarefa->getIdUsuario())) {
            throw new InvalidArgumentException('A disciplina informada não existe ou não pertence ao usuário.');
        }
    }

    /** Executa SELECT com a condição dada e guarda as linhas em uma lista encadeada. */
    private function montarLista(string $condicao, array $parametros): ListaEncadeada
    {
        $stmt = $this->executar(
            "SELECT * FROM tarefa WHERE {$condicao} ORDER BY prazo ASC, id_tarefa ASC",
            $parametros
        );

        $lista = new ListaEncadeada();
        while ($linha = $stmt->fetch()) {
            $lista->adicionarNoFim(Tarefa::deArray($linha));
        }

        return $lista;
    }
}
