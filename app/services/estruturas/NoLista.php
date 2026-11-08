<?php
declare(strict_types=1);

/**
 * Nó da lista encadeada: guarda um valor e a referência para o próximo nó.
 */
class NoLista
{
    public mixed $valor;
    public ?NoLista $proximo = null;

    public function __construct(mixed $valor)
    {
        $this->valor = $valor;
    }
}
