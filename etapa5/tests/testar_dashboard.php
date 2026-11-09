<?php
declare(strict_types=1);

/**
 * Testes do DashboardService (indicadores, filtros, busca e ordenação) e da Formatacao.
 *
 * Como executar (com o MySQL do XAMPP ligado e o banco importado):
 *   C:\xampp\php\php.exe tests\testar_dashboard.php
 *
 * Cria usuários "teste.dash.<código>@academiclear.test" e remove tudo ao final.
 * Os prazos são calculados a partir de hoje, então o teste vale em qualquer data.
 * A aparência da tela está no roteiro manual do README.
 */

require_once __DIR__ . '/testar_repositorios.php'; // reaproveita verificar(), verificarExcecao() e secao()

/** Data AAAA-MM-DD deslocada em N dias a partir de hoje (negativo = passado). */
function diasA(int $dias): string
{
    return date('Y-m-d', strtotime(($dias >= 0 ? '+' : '') . $dias . ' days'));
}

/** Títulos de uma coleção de tarefas, na ordem, para comparar listas. */
function titulosDe(iterable $tarefas): array
{
    $titulos = [];
    foreach ($tarefas as $tarefa) {
        $titulos[] = $tarefa->getTitulo();
    }
    return $titulos;
}

/** @return int quantidade de falhas */
function executarTestesDashboard(PDO $pdo): int
{
    $usuarios = new UsuarioRepository($pdo);
    $disciplinas = new DisciplinaRepository($pdo);
    $tarefas = new TarefaRepository($pdo, $disciplinas);
    $servico = new DashboardService($tarefas, $disciplinas);

    $codigo = bin2hex(random_bytes(4));
    $idsParaLimpar = [];

    try {
        // ---------------------------------------------------------------
        // Dados de teste
        // ---------------------------------------------------------------
        $ana = $usuarios->cadastrar(Usuario::novo('Ana Dash', "teste.dash.a.{$codigo}@academiclear.test", 'senha1234'));
        $bruno = $usuarios->cadastrar(Usuario::novo('Bruno Dash', "teste.dash.b.{$codigo}@academiclear.test", 'senha1234'));
        $carla = $usuarios->cadastrar(Usuario::novo('Carla Dash', "teste.dash.c.{$codigo}@academiclear.test", 'senha1234'));
        array_push($idsParaLimpar, $ana->getIdUsuario(), $bruno->getIdUsuario(), $carla->getIdUsuario());
        $idAna = $ana->getIdUsuario();

        $calculo = $disciplinas->cadastrar(new Disciplina(null, 'Cálculo', $idAna));
        $qualidade = $disciplinas->cadastrar(new Disciplina(null, 'Qualidade de Software', $idAna));
        $disciplinas->cadastrar(new Disciplina(null, 'Vazia', $idAna)); // sem tarefas
        $discBruno = $disciplinas->cadastrar(new Disciplina(null, 'Banco de Dados', $bruno->getIdUsuario()));
        $idCalculo = $calculo->getIdDisciplina();
        $idQualidade = $qualidade->getIdDisciplina();

        $novaTarefa = function (string $titulo, int $dias, string $prioridade, string $status, Disciplina $disc, int $idUsuario) use ($tarefas): void {
            $tarefas->cadastrar(new Tarefa(null, $titulo, null, diasA($dias), $prioridade, $status, null, $disc->getIdDisciplina(), $idUsuario));
        };

        //                titulo               prazo prioridade                    status                          disciplina
        $novaTarefa('Lista de Revisão',  -3, Tarefa::PRIORIDADE_ALTA,  Tarefa::STATUS_PENDENTE,     $calculo,   $idAna); // atrasada
        $novaTarefa('Relatório final',   -1, Tarefa::PRIORIDADE_MEDIA, Tarefa::STATUS_EM_ANDAMENTO, $qualidade, $idAna); // atrasada
        $novaTarefa('Prova',              0, Tarefa::PRIORIDADE_ALTA,  Tarefa::STATUS_EM_ANDAMENTO, $calculo,   $idAna); // vence hoje (NÃO atrasada)
        $novaTarefa('Revisão',            2, Tarefa::PRIORIDADE_BAIXA, Tarefa::STATUS_PENDENTE,     $qualidade, $idAna);
        $novaTarefa('Entregue',         -10, Tarefa::PRIORIDADE_MEDIA, Tarefa::STATUS_CONCLUIDA,    $calculo,   $idAna); // prazo passado, mas concluída
        $novaTarefa('Seminário',         30, Tarefa::PRIORIDADE_BAIXA, Tarefa::STATUS_PENDENTE,     $qualidade, $idAna);
        $novaTarefa('Extra',             40, Tarefa::PRIORIDADE_BAIXA, Tarefa::STATUS_PENDENTE,     $qualidade, $idAna);
        $novaTarefa('Tarefa do Bruno',    1, Tarefa::PRIORIDADE_ALTA,  Tarefa::STATUS_PENDENTE,     $discBruno, $bruno->getIdUsuario());

        // ---------------------------------------------------------------
        secao('1. Indicadores');
        $painel = $servico->montar($idAna, []);
        $ind = $painel['indicadores'];
        verificar('total = 7', $ind['total'] === 7);
        verificar('pendentes = 4', $ind['pendentes'] === 4);
        verificar('em andamento = 2', $ind['em_andamento'] === 2);
        verificar('concluídas = 1', $ind['concluidas'] === 1);
        verificar('atrasadas = 2 (a que vence hoje e a concluída não contam)', $ind['atrasadas'] === 2);
        verificar('percentual concluído = 14%', $ind['percentual_concluido'] === 14);
        verificar('totalTarefas = 7', $painel['totalTarefas'] === 7);

        secao('2. Progresso por disciplina');
        $porDisc = $painel['porDisciplina'];
        verificar('3 disciplinas, em ordem alfabética', array_column($porDisc, 'nome') === ['Cálculo', 'Qualidade de Software', 'Vazia']);
        verificar('Cálculo: 1 de 3 concluídas (33%)', $porDisc[0]['total'] === 3 && $porDisc[0]['concluidas'] === 1 && $porDisc[0]['percentual'] === 33);
        verificar('Qualidade: 0 de 4 concluídas (0%)', $porDisc[1]['total'] === 4 && $porDisc[1]['concluidas'] === 0 && $porDisc[1]['percentual'] === 0);
        verificar('disciplina sem tarefas aparece com zeros', $porDisc[2]['total'] === 0 && $porDisc[2]['percentual'] === 0);
        verificar('nomesDisciplinas mapeia ID para nome', ($painel['nomesDisciplinas'][$idCalculo] ?? null) === 'Cálculo');

        secao('3. Próximos prazos');
        verificar('mostra no máximo 5, só abertas, atrasadas primeiro', titulosDe($painel['proximos']) === ['Lista de Revisão', 'Relatório final', 'Prova', 'Revisão', 'Seminário']);
        verificar('não inclui concluída nem a 6ª aberta', !in_array('Entregue', titulosDe($painel['proximos']), true) && !in_array('Extra', titulosDe($painel['proximos']), true));

        secao('4. Filtros (um por vez)');
        $titulos = fn (array $consulta): array => titulosDe($servico->montar($idAna, $consulta)['tarefas']);
        verificar('sem filtros: 7 tarefas ordenadas por prazo', $titulos([]) === ['Entregue', 'Lista de Revisão', 'Relatório final', 'Prova', 'Revisão', 'Seminário', 'Extra']);
        verificar('status=pendente', $titulos(['status' => 'pendente']) === ['Lista de Revisão', 'Revisão', 'Seminário', 'Extra']);
        verificar('status=em_andamento', $titulos(['status' => 'em_andamento']) === ['Relatório final', 'Prova']);
        verificar('status=concluida', $titulos(['status' => 'concluida']) === ['Entregue']);
        verificar('status=atrasada', $titulos(['status' => 'atrasada']) === ['Lista de Revisão', 'Relatório final']);
        verificar('prioridade=alta', $titulos(['prioridade' => 'alta']) === ['Lista de Revisão', 'Prova']);
        verificar('disciplina=Qualidade', $titulos(['disciplina' => (string) $idQualidade]) === ['Relatório final', 'Revisão', 'Seminário', 'Extra']);

        secao('5. Busca por título');
        verificar('busca simples', $titulos(['q' => 'prova']) === ['Prova']);
        verificar('ignora maiúsculas', $titulos(['q' => 'PROVA']) === ['Prova']);
        verificar('ignora acentos (revisao acha "Revisão")', $titulos(['q' => 'revisao']) === ['Lista de Revisão', 'Revisão']);
        verificar('aceita acento na busca (REVISÃO)', $titulos(['q' => 'REVISÃO']) === ['Lista de Revisão', 'Revisão']);
        verificar('busca parcial no meio do título', $titulos(['q' => 'rio fin']) === ['Relatório final']);
        verificar('espaços nas pontas são ignorados', $titulos(['q' => '   prova   ']) === ['Prova']);
        verificar('sem resultado devolve lista vazia', $titulos(['q' => 'inexistente']) === []);
        verificar('caracteres especiais não quebram (%, _, aspas)', $titulos(['q' => "%_'\""]) === []);

        secao('6. Filtros combinados');
        verificar('Cálculo + alta', $titulos(['disciplina' => (string) $idCalculo, 'prioridade' => 'alta']) === ['Lista de Revisão', 'Prova']);
        verificar('Cálculo + alta + atrasada', $titulos(['disciplina' => (string) $idCalculo, 'prioridade' => 'alta', 'status' => 'atrasada']) === ['Lista de Revisão']);
        verificar('busca + status', $titulos(['q' => 'revisao', 'status' => 'pendente']) === ['Lista de Revisão', 'Revisão']);
        verificar('combinação sem interseção devolve vazio', $titulos(['status' => 'concluida', 'prioridade' => 'alta']) === []);

        secao('7. Ordenação');
        verificar('prazo_desc', $titulos(['ordenar' => 'prazo_desc']) === ['Extra', 'Seminário', 'Revisão', 'Prova', 'Relatório final', 'Lista de Revisão', 'Entregue']);
        verificar('prioridade: alta, média, baixa (empate por prazo)', $titulos(['ordenar' => 'prioridade']) === ['Lista de Revisão', 'Prova', 'Entregue', 'Relatório final', 'Revisão', 'Seminário', 'Extra']);
        verificar('titulo: A a Z sem considerar acento', $titulos(['ordenar' => 'titulo']) === ['Entregue', 'Extra', 'Lista de Revisão', 'Prova', 'Relatório final', 'Revisão', 'Seminário']);
        verificar('ordenar + filtrar juntos', $titulos(['status' => 'pendente', 'ordenar' => 'prazo_desc']) === ['Extra', 'Seminário', 'Revisão', 'Lista de Revisão']);
        verificar('indicadores não mudam quando há filtro', $servico->montar($idAna, ['status' => 'concluida'])['indicadores']['total'] === 7);

        secao('8. Validação dos parâmetros da URL');
        $idsAna = [$idCalculo, $idQualidade];
        $padrao = ['q' => '', 'disciplina' => null, 'status' => '', 'prioridade' => '', 'ordenar' => 'prazo_asc'];
        verificar('sem parâmetros: valores padrão', DashboardService::normalizarFiltros([], $idsAna) === $padrao);
        verificar('valores inválidos viram o padrão', DashboardService::normalizarFiltros(['status' => 'hack', 'prioridade' => 'urgente', 'ordenar' => 'DROP TABLE', 'disciplina' => 'abc'], $idsAna) === $padrao);
        verificar('arrays no lugar de texto são ignorados', DashboardService::normalizarFiltros(['q' => ['x'], 'status' => ['pendente'], 'disciplina' => ['1']], $idsAna) === $padrao);
        verificar('disciplina negativa ou com símbolos é ignorada', DashboardService::normalizarFiltros(['disciplina' => '-1'], $idsAna)['disciplina'] === null && DashboardService::normalizarFiltros(['disciplina' => '1; DROP'], $idsAna)['disciplina'] === null);
        verificar('número gigante não estoura', DashboardService::normalizarFiltros(['disciplina' => '99999999999999999999'], $idsAna)['disciplina'] === null);
        verificar('busca é cortada em ' . DashboardService::BUSCA_MAXIMA . ' caracteres', mb_strlen(DashboardService::normalizarFiltros(['q' => str_repeat('á', 500)], $idsAna)['q']) === DashboardService::BUSCA_MAXIMA);
        verificar('disciplina válida do usuário é aceita', DashboardService::normalizarFiltros(['disciplina' => (string) $idCalculo], $idsAna)['disciplina'] === $idCalculo);

        secao('9. Isolamento entre usuários');
        $painelBruno = $servico->montar($bruno->getIdUsuario(), []);
        verificar('Bruno vê só a própria tarefa', titulosDe($painelBruno['tarefas']) === ['Tarefa do Bruno'] && $painelBruno['indicadores']['total'] === 1);
        verificar('Ana não vê a tarefa do Bruno', !in_array('Tarefa do Bruno', $titulos([]), true));
        $comDisciplinaAlheia = $servico->montar($idAna, ['disciplina' => (string) $discBruno->getIdDisciplina()]);
        verificar('disciplina de outro usuário é ignorada (não filtra nem vaza)', $comDisciplinaAlheia['filtros']['disciplina'] === null && count($comDisciplinaAlheia['tarefas']) === 7);
        verificar('lista de disciplinas é só do usuário', count($painelBruno['disciplinas']) === 1 && $painelBruno['disciplinas'][0]->getNome() === 'Banco de Dados');

        secao('10. Usuário sem nenhum dado');
        $vazio = $servico->montar($carla->getIdUsuario(), []);
        verificar('indicadores zerados e sem divisão por zero', $vazio['indicadores'] === ['total' => 0, 'pendentes' => 0, 'em_andamento' => 0, 'concluidas' => 0, 'atrasadas' => 0, 'percentual_concluido' => 0]);
        verificar('sem tarefas, disciplinas nem próximos prazos', count($vazio['tarefas']) === 0 && $vazio['porDisciplina'] === [] && $vazio['proximos'] === []);
        verificar('filtrar usuário vazio não dá erro', count($servico->montar($carla->getIdUsuario(), ['q' => 'x', 'status' => 'atrasada', 'ordenar' => 'titulo'])['tarefas']) === 0);

        secao('11. Flag de filtros ativos');
        verificar('sem filtros: inativo', $servico->montar($idAna, [])['filtrosAtivos'] === false);
        verificar('só ordenar não conta como filtro', $servico->montar($idAna, ['ordenar' => 'titulo'])['filtrosAtivos'] === false);
        verificar('com status: ativo', $servico->montar($idAna, ['status' => 'pendente'])['filtrosAtivos'] === true);
        verificar('com busca: ativo', $servico->montar($idAna, ['q' => 'a'])['filtrosAtivos'] === true);

        secao('12. Formatacao (textos da tela)');
        $tarefaEm = fn (int $dias, string $status = Tarefa::STATUS_PENDENTE): Tarefa => new Tarefa(1, 'T', null, diasA($dias), Tarefa::PRIORIDADE_MEDIA, $status, null, 1, 1);
        verificar('atrasada há 3 dias', Formatacao::situacaoPrazo($tarefaEm(-3)) === 'Atrasada há 3 dias');
        verificar('atrasada há 1 dia (singular)', Formatacao::situacaoPrazo($tarefaEm(-1)) === 'Atrasada há 1 dia');
        verificar('vence hoje', Formatacao::situacaoPrazo($tarefaEm(0)) === 'Vence hoje');
        verificar('vence amanhã', Formatacao::situacaoPrazo($tarefaEm(1)) === 'Vence amanhã');
        verificar('vence em 5 dias', Formatacao::situacaoPrazo($tarefaEm(5)) === 'Vence em 5 dias');
        verificar('concluída não mostra situação de prazo', Formatacao::situacaoPrazo($tarefaEm(-3, Tarefa::STATUS_CONCLUIDA)) === '' && Formatacao::classePrazo($tarefaEm(-3, Tarefa::STATUS_CONCLUIDA)) === '');
        verificar('classe do prazo: atrasada / hoje / nenhuma', Formatacao::classePrazo($tarefaEm(-2)) === 'atrasada' && Formatacao::classePrazo($tarefaEm(0)) === 'hoje' && Formatacao::classePrazo($tarefaEm(4)) === '');
        verificar('situação e Tarefa::estaAtrasada concordam', Formatacao::classePrazo($tarefaEm(-2)) === 'atrasada' && $tarefaEm(-2)->estaAtrasada() && !$tarefaEm(0)->estaAtrasada());
        verificar('data AAAA-MM-DD vira DD/MM/AAAA', Formatacao::data('2026-11-08') === '08/11/2026');
        verificar('data em formato inesperado é devolvida como veio', Formatacao::data('xx') === 'xx');
        verificar('rótulos de status e prioridade', Formatacao::status('em_andamento') === 'Em andamento' && Formatacao::status('concluida') === 'Concluída' && Formatacao::prioridade('media') === 'Média');
        verificar('quantidade: singular e plural', Formatacao::quantidade(1, 'tarefa', 'tarefas') === '1 tarefa' && Formatacao::quantidade(0, 'tarefa', 'tarefas') === '0 tarefas');
        verificar('resumo corta texto longo com reticências', mb_strlen(Formatacao::resumo(str_repeat('ab ', 100))) <= 140 && str_ends_with(Formatacao::resumo(str_repeat('ab ', 100)), '…'));
        verificar('resumo junta quebras de linha e aceita null', Formatacao::resumo("linha 1\n\n  linha 2") === 'linha 1 linha 2' && Formatacao::resumo(null) === '');
    } finally {
        // Limpeza: apaga os usuários de teste (disciplinas e tarefas saem por cascata).
        foreach ($idsParaLimpar as $id) {
            $usuarios->excluir($id);
        }
    }

    return $GLOBALS['testes_falhas'];
}

// Executa automaticamente apenas quando chamado direto pelo terminal.
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    try {
        $falhas = executarTestesDashboard(Conexao::obter());
    } catch (Throwable $e) {
        echo PHP_EOL . 'ERRO: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }

    echo PHP_EOL . "Resultado: {$GLOBALS['testes_ok']} OK, {$falhas} falha(s)." . PHP_EOL;
    exit($falhas === 0 ? 0 : 1);
}
