<?php
/**
 * Dashboard do usuário autenticado.
 *
 * Variáveis: $usuario (Usuario), $csrf, $flash,
 *   $filtros, $filtrosAtivos, $disciplinas (Disciplina[]), $nomesDisciplinas (id => nome),
 *   $indicadores, $porDisciplina, $proximos (Tarefa[]), $tarefas (ListaEncadeada), $totalTarefas.
 */
$tituloPagina = 'Dashboard';
$classeCorpo = 'pagina-interna';
require __DIR__ . '/../compartilhado/cabecalho.php';

/** Monta o endereço do dashboard com os parâmetros dados (já pronto para usar em href). */
$link = static function (array $parametros = []): string {
    $consulta = http_build_query($parametros);
    return Visao::e('dashboard.php' . ($consulta !== '' ? '?' . $consulta : ''));
};
$selecionado = static fn (mixed $atual, mixed $valor): string => (string) $atual === (string) $valor ? ' selected' : '';

$abertas = $indicadores['pendentes'] + $indicadores['em_andamento'];
$mostrarLimpar = $filtrosAtivos || $filtros['ordenar'] !== DashboardService::ORDEM_PADRAO;
$quantidadeExibida = count($tarefas);
?>
<header class="topo">
    <div class="topo__conteudo">
        <div class="marca"><span class="marca__icone" aria-hidden="true">A</span><span>AcademiClear</span></div>
        <div class="topo__usuario">
            <span class="topo__nome"><?= Visao::e($usuario->getNome()) ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= Visao::e($csrf) ?>">
                <button type="submit" class="botao botao--secundario">Sair</button>
            </form>
        </div>
    </div>
</header>

<main class="conteudo">
    <?php require __DIR__ . '/../compartilhado/alertas.php'; ?>

    <h1>Olá, <?= Visao::e(explode(' ', $usuario->getNome())[0]) ?>!</h1>
    <p class="subtitulo">
        <?php if ($totalTarefas === 0): ?>
            Você ainda não tem tarefas cadastradas.
        <?php elseif ($abertas === 0): ?>
            Tudo em dia: todas as suas tarefas estão concluídas.
        <?php else: ?>
            Você tem <?= Visao::e(Formatacao::quantidade($abertas, 'tarefa em aberto', 'tarefas em aberto')) ?><?php
            if ($indicadores['atrasadas'] > 0): ?>, <strong><?= Visao::e(Formatacao::quantidade($indicadores['atrasadas'], 'atrasada', 'atrasadas')) ?></strong><?php endif; ?>.
        <?php endif; ?>
    </p>

    <!-- ===== Indicadores ===== -->
    <section aria-labelledby="titulo-indicadores">
        <h2 id="titulo-indicadores" class="visualmente-oculto">Indicadores</h2>
        <ul class="indicadores">
            <li><a class="indicador" href="<?= $link() ?>">
                <span class="indicador__numero"><?= (int) $indicadores['total'] ?></span>
                <span class="indicador__rotulo">Total</span>
            </a></li>
            <li><a class="indicador" href="<?= $link(['status' => Tarefa::STATUS_PENDENTE]) ?>">
                <span class="indicador__numero"><?= (int) $indicadores['pendentes'] ?></span>
                <span class="indicador__rotulo">Pendentes</span>
            </a></li>
            <li><a class="indicador indicador--andamento" href="<?= $link(['status' => Tarefa::STATUS_EM_ANDAMENTO]) ?>">
                <span class="indicador__numero"><?= (int) $indicadores['em_andamento'] ?></span>
                <span class="indicador__rotulo">Em andamento</span>
            </a></li>
            <li><a class="indicador indicador--concluida" href="<?= $link(['status' => Tarefa::STATUS_CONCLUIDA]) ?>">
                <span class="indicador__numero"><?= (int) $indicadores['concluidas'] ?></span>
                <span class="indicador__rotulo">Concluídas</span>
            </a></li>
            <li><a class="indicador<?= $indicadores['atrasadas'] > 0 ? ' indicador--atrasada' : '' ?>" href="<?= $link(['status' => Formatacao::STATUS_ATRASADA]) ?>">
                <span class="indicador__numero"><?= (int) $indicadores['atrasadas'] ?></span>
                <span class="indicador__rotulo">Atrasadas</span>
            </a></li>
        </ul>

        <div class="progresso-geral">
            <div class="progresso-geral__texto">
                <span>Progresso geral</span>
                <strong><?= (int) $indicadores['percentual_concluido'] ?>% concluído</strong>
            </div>
            <div class="progresso" role="progressbar" aria-label="Tarefas concluídas"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $indicadores['percentual_concluido'] ?>">
                <div class="progresso__barra" style="width: <?= (int) $indicadores['percentual_concluido'] ?>%"></div>
            </div>
        </div>
    </section>

    <!-- ===== Próximos prazos e progresso por disciplina ===== -->
    <div class="paineis">
        <section class="painel" aria-labelledby="titulo-proximos">
            <h2 id="titulo-proximos" class="painel__titulo">Próximos prazos</h2>
            <?php if ($proximos === []): ?>
                <p class="painel__vazio">Nenhuma tarefa em aberto.</p>
            <?php else: ?>
                <ol class="lista-simples">
                    <?php foreach ($proximos as $proxima): ?>
                        <li class="lista-simples__item">
                            <span class="lista-simples__titulo"><?= Visao::e($proxima->getTitulo()) ?></span>
                            <span class="lista-simples__meta">
                                <?= Visao::e($nomesDisciplinas[$proxima->getIdDisciplina()] ?? '') ?>
                                · <?= Visao::e(Formatacao::data($proxima->getPrazo())) ?>
                                <?php $classe = Formatacao::classePrazo($proxima); ?>
                                <span class="prazo<?= $classe !== '' ? ' prazo--' . $classe : '' ?>">(<?= Visao::e(Formatacao::situacaoPrazo($proxima)) ?>)</span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

        <section class="painel" aria-labelledby="titulo-disciplinas">
            <h2 id="titulo-disciplinas" class="painel__titulo">Por disciplina</h2>
            <?php if ($porDisciplina === []): ?>
                <p class="painel__vazio">Nenhuma disciplina cadastrada.</p>
            <?php else: ?>
                <ul class="lista-simples">
                    <?php foreach ($porDisciplina as $resumo): ?>
                        <li class="lista-simples__item">
                            <a class="lista-simples__titulo" href="<?= $link(['disciplina' => $resumo['id']]) ?>"><?= Visao::e($resumo['nome']) ?></a>
                            <span class="lista-simples__meta">
                                <?= $resumo['total'] === 0
                                    ? 'Sem tarefas'
                                    : Visao::e($resumo['concluidas'] . ' de ' . Formatacao::quantidade($resumo['total'], 'concluída', 'concluídas')) ?>
                            </span>
                            <div class="progresso progresso--fino" role="progressbar"
                                 aria-label="Progresso em <?= Visao::e($resumo['nome']) ?>"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $resumo['percentual'] ?>">
                                <div class="progresso__barra" style="width: <?= (int) $resumo['percentual'] ?>%"></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

    <!-- ===== Busca e filtros ===== -->
    <section aria-labelledby="titulo-tarefas">
        <h2 id="titulo-tarefas" class="secao-titulo">Tarefas</h2>

        <form method="get" action="dashboard.php" class="filtros" role="search" aria-label="Buscar e filtrar tarefas">
            <div class="filtros__campo filtros__campo--busca">
                <label for="filtro-q">Buscar por título</label>
                <input type="search" id="filtro-q" name="q" value="<?= Visao::e($filtros['q']) ?>"
                       maxlength="<?= DashboardService::BUSCA_MAXIMA ?>" placeholder="Ex.: relatório" autocomplete="off">
            </div>

            <div class="filtros__campo">
                <label for="filtro-disciplina">Disciplina</label>
                <select id="filtro-disciplina" name="disciplina">
                    <option value="">Todas</option>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option value="<?= (int) $disciplina->getIdDisciplina() ?>"<?= $selecionado($filtros['disciplina'], $disciplina->getIdDisciplina()) ?>><?= Visao::e($disciplina->getNome()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtros__campo">
                <label for="filtro-status">Status</label>
                <select id="filtro-status" name="status">
                    <option value="">Todos</option>
                    <?php foreach ([...Tarefa::STATUS, Formatacao::STATUS_ATRASADA] as $status): ?>
                        <option value="<?= Visao::e($status) ?>"<?= $selecionado($filtros['status'], $status) ?>><?= Visao::e(Formatacao::status($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtros__campo">
                <label for="filtro-prioridade">Prioridade</label>
                <select id="filtro-prioridade" name="prioridade">
                    <option value="">Todas</option>
                    <?php foreach (Tarefa::PRIORIDADES as $prioridade): ?>
                        <option value="<?= Visao::e($prioridade) ?>"<?= $selecionado($filtros['prioridade'], $prioridade) ?>><?= Visao::e(Formatacao::prioridade($prioridade)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtros__campo">
                <label for="filtro-ordenar">Ordenar por</label>
                <select id="filtro-ordenar" name="ordenar">
                    <?php foreach (DashboardService::ORDENACOES as $valor => $rotulo): ?>
                        <option value="<?= Visao::e($valor) ?>"<?= $selecionado($filtros['ordenar'], $valor) ?>><?= Visao::e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtros__acoes">
                <button type="submit" class="botao botao--primario">Filtrar</button>
                <?php if ($mostrarLimpar): ?>
                    <a class="botao botao--secundario" href="<?= $link() ?>">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <p class="resultado" role="status">
            <?php if ($filtrosAtivos): ?>
                Mostrando <?= $quantidadeExibida ?> de <?= Visao::e(Formatacao::quantidade($totalTarefas, 'tarefa', 'tarefas')) ?>.
            <?php else: ?>
                <?= Visao::e(Formatacao::quantidade($totalTarefas, 'tarefa', 'tarefas')) ?>.
            <?php endif; ?>
        </p>

        <?php if ($totalTarefas === 0): ?>
            <div class="vazio"><p>Você ainda não cadastrou nenhuma tarefa.</p></div>
        <?php elseif ($quantidadeExibida === 0): ?>
            <div class="vazio">
                <p>Nenhuma tarefa corresponde aos filtros escolhidos.</p>
                <a class="botao botao--secundario" href="<?= $link() ?>">Limpar filtros</a>
            </div>
        <?php else: ?>
            <ul class="tarefas">
                <?php foreach ($tarefas as $tarefa): ?>
                    <?php
                    $classePrazo = Formatacao::classePrazo($tarefa);
                    $situacao = Formatacao::situacaoPrazo($tarefa);
                    $resumoDescricao = Formatacao::resumo($tarefa->getDescricao());
                    ?>
                    <li class="tarefa<?= $tarefa->getStatus() === Tarefa::STATUS_CONCLUIDA ? ' tarefa--concluida' : '' ?><?= $classePrazo === 'atrasada' ? ' tarefa--atrasada' : '' ?>">
                        <div class="tarefa__principal">
                            <h3 class="tarefa__titulo"><?= Visao::e($tarefa->getTitulo()) ?></h3>
                            <p class="tarefa__meta">
                                <?= Visao::e($nomesDisciplinas[$tarefa->getIdDisciplina()] ?? '') ?>
                                · Prazo: <?= Visao::e(Formatacao::data($tarefa->getPrazo())) ?>
                                <?php if ($situacao !== ''): ?>
                                    <span class="prazo<?= $classePrazo !== '' ? ' prazo--' . $classePrazo : '' ?>">(<?= Visao::e($situacao) ?>)</span>
                                <?php endif; ?>
                            </p>
                            <?php if ($resumoDescricao !== ''): ?>
                                <p class="tarefa__descricao"><?= Visao::e($resumoDescricao) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="tarefa__etiquetas">
                            <span class="etiqueta etiqueta--prioridade-<?= Visao::e($tarefa->getPrioridade()) ?>"><span class="visualmente-oculto">Prioridade </span><?= Visao::e(Formatacao::prioridade($tarefa->getPrioridade())) ?></span>
                            <span class="etiqueta etiqueta--status-<?= Visao::e($tarefa->getStatus()) ?>"><?= Visao::e(Formatacao::status($tarefa->getStatus())) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../compartilhado/rodape.php'; ?>
