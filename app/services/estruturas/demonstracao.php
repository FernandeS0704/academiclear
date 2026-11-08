<?php
declare(strict_types=1);

/**
 * Demonstração das estruturas de dados (não usa banco).
 *
 * Execute no terminal (XAMPP):
 *   C:\xampp\php\php.exe app\services\estruturas\demonstracao.php
 */

require_once __DIR__ . '/../../config/config.php';

function titulo(string $texto): void
{
    echo PHP_EOL . "=== {$texto} ===" . PHP_EOL;
}

$prazoBase = new DateTime('today');
$criar = function (int $id, string $titulo, int $dias, string $prioridade, string $status = Tarefa::STATUS_PENDENTE) use ($prazoBase): Tarefa {
    $prazo = (clone $prazoBase)->modify("+{$dias} days")->format('Y-m-d');
    return new Tarefa($id, $titulo, null, $prazo, $prioridade, $status, null, 1, 1);
};

// Simula as tarefas que viriam do banco, já ordenadas por prazo.
$lista = new ListaEncadeada();
$lista->adicionarNoFim($criar(10, 'Lista de exercícios de Cálculo', 2, Tarefa::PRIORIDADE_ALTA));
$lista->adicionarNoFim($criar(11, 'Relatório de Qualidade de Software', 5, Tarefa::PRIORIDADE_MEDIA, Tarefa::STATUS_EM_ANDAMENTO));
$lista->adicionarNoFim($criar(12, 'Seminário de Estruturas de Dados', 9, Tarefa::PRIORIDADE_BAIXA));

titulo('Lista encadeada: percorrendo');
foreach ($lista as $tarefa) {
    printf("#%d %s (prazo %s)%s", $tarefa->getIdTarefa(), $tarefa->getTitulo(), $tarefa->getPrazo(), PHP_EOL);
}
echo 'Tamanho: ' . count($lista) . PHP_EOL;

titulo('Lista encadeada: filtrando prioridade alta');
foreach ($lista->filtrar(fn(Tarefa $t) => $t->getPrioridade() === Tarefa::PRIORIDADE_ALTA) as $tarefa) {
    echo $tarefa->getTitulo() . PHP_EOL;
}

titulo('Lista encadeada: removendo a tarefa #11');
$lista->remover(fn(Tarefa $t) => $t->getIdTarefa() === 11);
echo 'Tamanho: ' . count($lista) . PHP_EOL;

titulo('Mapa por ID: busca direta');
$mapa = MapaTarefas::deColecao($lista);
$encontrada = $mapa->buscar(12);
echo $encontrada ? 'Tarefa #12 encontrada: ' . $encontrada->getTitulo() : 'Tarefa #12 não encontrada';
echo PHP_EOL;
echo 'Tarefa #11 existe? ' . ($mapa->existe(11) ? 'sim' : 'não') . PHP_EOL;
