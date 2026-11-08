<?php
declare(strict_types=1);

/**
 * "HashMap" de tarefas: array associativo indexado pelo ID da tarefa.
 *
 * O array do PHP já é uma tabela hash, então buscar, inserir e remover
 * por chave custa O(1) em média.
 *
 * Uso previsto: montar o mapa a partir da ListaEncadeada retornada pelo
 * TarefaRepository e localizar tarefas por ID nas telas de edição,
 * mudança de status e exclusão, sem percorrer a lista toda.
 */
class MapaTarefas
{
    /** @var array<int, Tarefa> */
    private array $tarefas = [];

    /** Constrói o mapa a partir de qualquer coleção iterável de tarefas. */
    public static function deColecao(iterable $tarefas): self
    {
        $mapa = new self();
        foreach ($tarefas as $tarefa) {
            $mapa->inserir($tarefa);
        }
        return $mapa;
    }

    public function inserir(Tarefa $tarefa): void
    {
        $id = $tarefa->getIdTarefa();
        if ($id === null) {
            throw new InvalidArgumentException('A tarefa precisa ter ID para entrar no mapa.');
        }
        $this->tarefas[$id] = $tarefa;
    }

    public function buscar(int $idTarefa): ?Tarefa
    {
        return $this->tarefas[$idTarefa] ?? null;
    }

    public function existe(int $idTarefa): bool
    {
        return isset($this->tarefas[$idTarefa]);
    }

    public function remover(int $idTarefa): void
    {
        unset($this->tarefas[$idTarefa]);
    }

    public function tamanho(): int
    {
        return count($this->tarefas);
    }

    /** @return array<int, Tarefa> */
    public function todas(): array
    {
        return $this->tarefas;
    }
}
