<?php
declare(strict_types=1);

/**
 * Lista encadeada simples, implementada com nós (NoLista) e referências.
 *
 * Uso previsto no sistema: o TarefaRepository devolve as tarefas lidas do
 * banco dentro de uma ListaEncadeada. Os módulos de busca por título,
 * filtros, ordenação por prazo e dashboard a percorrem com foreach.
 *
 * Mantemos uma referência para o último nó ($cauda), então inserir no fim
 * custa O(1) e a leitura do banco não precisa percorrer a lista a cada linha.
 */
class ListaEncadeada implements IteratorAggregate, Countable
{
    private ?NoLista $cabeca = null;
    private ?NoLista $cauda = null;
    private int $tamanho = 0;

    public function adicionarNoInicio(mixed $valor): void
    {
        $no = new NoLista($valor);
        $no->proximo = $this->cabeca;
        $this->cabeca = $no;

        if ($this->cauda === null) {
            $this->cauda = $no;
        }
        $this->tamanho++;
    }

    public function adicionarNoFim(mixed $valor): void
    {
        $no = new NoLista($valor);

        if ($this->cauda === null) {
            $this->cabeca = $no;
        } else {
            $this->cauda->proximo = $no;
        }
        $this->cauda = $no;
        $this->tamanho++;
    }

    /**
     * Remove o primeiro nó cujo valor satisfaz o critério.
     * Ex.: $lista->remover(fn($t) => $t->getIdTarefa() === 5);
     */
    public function remover(callable $criterio): bool
    {
        $anterior = null;
        $atual = $this->cabeca;

        while ($atual !== null) {
            if ($criterio($atual->valor)) {
                if ($anterior === null) {
                    $this->cabeca = $atual->proximo;
                } else {
                    $anterior->proximo = $atual->proximo;
                }
                if ($atual === $this->cauda) {
                    $this->cauda = $anterior;
                }
                $this->tamanho--;
                return true;
            }
            $anterior = $atual;
            $atual = $atual->proximo;
        }

        return false;
    }

    /** Devolve uma nova lista apenas com os valores que satisfazem o critério. */
    public function filtrar(callable $criterio): self
    {
        $resultado = new self();
        foreach ($this as $valor) {
            if ($criterio($valor)) {
                $resultado->adicionarNoFim($valor);
            }
        }
        return $resultado;
    }

    public function estaVazia(): bool
    {
        return $this->tamanho === 0;
    }

    public function primeiro(): mixed
    {
        return $this->cabeca?->valor;
    }

    public function count(): int
    {
        return $this->tamanho;
    }

    /** Percorre a lista seguindo a referência "proximo" de cada nó. */
    public function getIterator(): Generator
    {
        for ($no = $this->cabeca; $no !== null; $no = $no->proximo) {
            yield $no->valor;
        }
    }

    public function paraArray(): array
    {
        return iterator_to_array($this, false);
    }
}
