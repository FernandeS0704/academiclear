<?php
declare(strict_types=1);

/**
 * Acesso ao banco para a tabela "disciplina".
 *
 * Toda operação recebe o id_usuario e o usa no WHERE: um usuário só consegue
 * consultar, alterar ou excluir as próprias disciplinas.
 */
class DisciplinaRepository extends RepositorioBase
{
    /**
     * @throws RegistroDuplicadoException se o usuário já tiver uma disciplina com esse nome
     */
    public function cadastrar(Disciplina $disciplina): Disciplina
    {
        $this->executar(
            'INSERT INTO disciplina (nome, id_usuario) VALUES (:nome, :id_usuario)',
            [':nome' => $disciplina->getNome(), ':id_usuario' => $disciplina->getIdUsuario()],
            'Você já possui uma disciplina com esse nome.'
        );

        $disciplina->setIdDisciplina((int) $this->pdo->lastInsertId());
        return $disciplina;
    }

    public function buscarPorId(int $idDisciplina, int $idUsuario): ?Disciplina
    {
        $linha = $this->buscarLinha(
            'SELECT * FROM disciplina WHERE id_disciplina = :id AND id_usuario = :id_usuario',
            [':id' => $idDisciplina, ':id_usuario' => $idUsuario]
        );
        return $linha ? Disciplina::deArray($linha) : null;
    }

    /** @return Disciplina[] */
    public function listarPorUsuario(int $idUsuario): array
    {
        $linhas = $this->buscarLinhas(
            'SELECT * FROM disciplina WHERE id_usuario = :id_usuario ORDER BY nome',
            [':id_usuario' => $idUsuario]
        );
        return array_map([Disciplina::class, 'deArray'], $linhas);
    }

    /**
     * @return bool false se a disciplina não existir ou não for do usuário
     * @throws RegistroDuplicadoException se o novo nome já existir para o usuário
     */
    public function atualizar(Disciplina $disciplina): bool
    {
        $id = $disciplina->getIdDisciplina();
        $idUsuario = $disciplina->getIdUsuario();

        if ($id === null || !$this->pertenceAoUsuario($id, $idUsuario)) {
            return false;
        }

        $this->executar(
            'UPDATE disciplina SET nome = :nome WHERE id_disciplina = :id AND id_usuario = :id_usuario',
            [':nome' => $disciplina->getNome(), ':id' => $id, ':id_usuario' => $idUsuario],
            'Você já possui uma disciplina com esse nome.'
        );

        return true;
    }

    /** Exclui a disciplina do usuário (as tarefas dela saem junto, por ON DELETE CASCADE). */
    public function excluir(int $idDisciplina, int $idUsuario): bool
    {
        $stmt = $this->executar(
            'DELETE FROM disciplina WHERE id_disciplina = :id AND id_usuario = :id_usuario',
            [':id' => $idDisciplina, ':id_usuario' => $idUsuario]
        );
        return $stmt->rowCount() > 0;
    }

    /** Confere se a disciplina existe e pertence ao usuário (usado na validação de tarefas). */
    public function pertenceAoUsuario(int $idDisciplina, int $idUsuario): bool
    {
        return $this->buscarPorId($idDisciplina, $idUsuario) !== null;
    }
}
