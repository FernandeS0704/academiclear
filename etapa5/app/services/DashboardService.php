<?php
declare(strict_types=1);

/**
 * Regras do dashboard: indicadores, filtros, busca por título e ordenação.
 *
 * Não conhece HTML, sessão nem $_GET diretamente (o Controller repassa os
 * parâmetros). Lê os dados pelos repositories já existentes.
 *
 * Os filtros percorrem a ListaEncadeada devolvida pelo TarefaRepository
 * (ListaEncadeada::filtrar), como previsto na documentação da estrutura.
 * Os indicadores são sempre calculados sobre TODAS as tarefas do usuário,
 * para que os números do topo não mudem quando um filtro é aplicado.
 */
class DashboardService
{
    public const ORDEM_PADRAO = 'prazo_asc';

    /** Critérios de ordenação aceitos (chave = valor do parâmetro "ordenar"). */
    public const ORDENACOES = [
        'prazo_asc'  => 'Prazo: mais próximo primeiro',
        'prazo_desc' => 'Prazo: mais distante primeiro',
        'prioridade' => 'Prioridade: alta primeiro',
        'titulo'     => 'Título: A a Z',
    ];

    public const BUSCA_MAXIMA = 100;
    private const QUANTIDADE_PROXIMOS = 5;

    private const ORDEM_PRIORIDADE = [
        Tarefa::PRIORIDADE_ALTA  => 0,
        Tarefa::PRIORIDADE_MEDIA => 1,
        Tarefa::PRIORIDADE_BAIXA => 2,
    ];

    /** Letras acentuadas (já em minúsculas) e a versão sem acento, para a busca ignorar acentos. */
    private const SEM_ACENTO = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
    ];

    private TarefaRepository $tarefas;
    private DisciplinaRepository $disciplinas;

    public function __construct(?TarefaRepository $tarefas = null, ?DisciplinaRepository $disciplinas = null)
    {
        $this->disciplinas = $disciplinas ?? new DisciplinaRepository();
        $this->tarefas = $tarefas ?? new TarefaRepository(null, $this->disciplinas);
    }

    /**
     * Reúne tudo o que o dashboard exibe.
     *
     * @param array<string, mixed> $consulta parâmetros brutos da URL ($_GET)
     * @return array{
     *     filtros: array{q: string, disciplina: ?int, status: string, prioridade: string, ordenar: string},
     *     filtrosAtivos: bool,
     *     disciplinas: Disciplina[],
     *     nomesDisciplinas: array<int, string>,
     *     indicadores: array<string, int>,
     *     porDisciplina: array<int, array<string, mixed>>,
     *     proximos: Tarefa[],
     *     tarefas: ListaEncadeada,
     *     totalTarefas: int
     * }
     * @throws RepositorioException se o banco falhar
     */
    public function montar(int $idUsuario, array $consulta): array
    {
        $disciplinas = $this->disciplinas->listarPorUsuario($idUsuario);
        $todas = $this->tarefas->listarPorUsuario($idUsuario);

        $idsDisciplinas = array_map(static fn (Disciplina $d): int => (int) $d->getIdDisciplina(), $disciplinas);
        $filtros = self::normalizarFiltros($consulta, $idsDisciplinas);

        $nomes = [];
        foreach ($disciplinas as $disciplina) {
            $nomes[(int) $disciplina->getIdDisciplina()] = $disciplina->getNome();
        }

        return [
            'filtros'          => $filtros,
            'filtrosAtivos'    => self::temFiltroAtivo($filtros),
            'disciplinas'      => $disciplinas,
            'nomesDisciplinas' => $nomes,
            'indicadores'      => $this->calcularIndicadores($todas),
            'porDisciplina'    => $this->resumirPorDisciplina($disciplinas, $todas),
            'proximos'         => $this->proximosPrazos($todas),
            'tarefas'          => self::ordenar(self::filtrar($todas, $filtros), $filtros['ordenar']),
            'totalTarefas'     => count($todas),
        ];
    }

    /**
     * Valida os parâmetros da URL. Qualquer valor desconhecido ou malformado
     * é descartado (volta ao padrão), nunca causa erro.
     *
     * @param array<string, mixed> $consulta
     * @param int[]                $idsDisciplinasValidos disciplinas do usuário
     * @return array{q: string, disciplina: ?int, status: string, prioridade: string, ordenar: string}
     */
    public static function normalizarFiltros(array $consulta, array $idsDisciplinasValidos): array
    {
        $busca = isset($consulta['q']) && is_string($consulta['q']) ? trim($consulta['q']) : '';

        $disciplina = null;
        $bruta = $consulta['disciplina'] ?? null;
        if (is_string($bruta) && ctype_digit($bruta) && in_array((int) $bruta, $idsDisciplinasValidos, true)) {
            $disciplina = (int) $bruta;
        }

        return [
            'q'          => mb_substr($busca, 0, self::BUSCA_MAXIMA),
            'disciplina' => $disciplina,
            'status'     => self::valorPermitido($consulta['status'] ?? null, [...Tarefa::STATUS, Formatacao::STATUS_ATRASADA], ''),
            'prioridade' => self::valorPermitido($consulta['prioridade'] ?? null, Tarefa::PRIORIDADES, ''),
            'ordenar'    => self::valorPermitido($consulta['ordenar'] ?? null, array_keys(self::ORDENACOES), self::ORDEM_PADRAO),
        ];
    }

    /** Texto em minúsculas e sem acentos: "Revisão" e "revisao" ficam iguais na busca. */
    public static function normalizarTexto(string $texto): string
    {
        return strtr(mb_strtolower($texto, 'UTF-8'), self::SEM_ACENTO);
    }

    // ---------------------------------------------------------------
    // Filtros e ordenação
    // ---------------------------------------------------------------

    /** Devolve uma nova lista só com as tarefas que atendem a todos os filtros ativos. */
    private static function filtrar(ListaEncadeada $todas, array $filtros): ListaEncadeada
    {
        $busca = self::normalizarTexto($filtros['q']);

        return $todas->filtrar(static function (Tarefa $tarefa) use ($filtros, $busca): bool {
            if ($busca !== '' && !str_contains(self::normalizarTexto($tarefa->getTitulo()), $busca)) {
                return false;
            }
            if ($filtros['disciplina'] !== null && $tarefa->getIdDisciplina() !== $filtros['disciplina']) {
                return false;
            }
            if ($filtros['prioridade'] !== '' && $tarefa->getPrioridade() !== $filtros['prioridade']) {
                return false;
            }

            return match ($filtros['status']) {
                ''                            => true,
                Formatacao::STATUS_ATRASADA   => $tarefa->estaAtrasada(),
                default                       => $tarefa->getStatus() === $filtros['status'],
            };
        });
    }

    /** Reordena (a lista de entrada não é alterada). A ordenação é estável: empates mantêm a ordem do banco. */
    private static function ordenar(ListaEncadeada $lista, string $criterio): ListaEncadeada
    {
        $itens = $lista->paraArray();

        usort($itens, match ($criterio) {
            'prazo_desc' => static fn (Tarefa $a, Tarefa $b): int => $b->getPrazo() <=> $a->getPrazo(),
            'prioridade' => static fn (Tarefa $a, Tarefa $b): int =>
                [self::ORDEM_PRIORIDADE[$a->getPrioridade()], $a->getPrazo()]
                <=> [self::ORDEM_PRIORIDADE[$b->getPrioridade()], $b->getPrazo()],
            'titulo'     => static fn (Tarefa $a, Tarefa $b): int =>
                strcmp(self::normalizarTexto($a->getTitulo()), self::normalizarTexto($b->getTitulo())),
            default      => static fn (Tarefa $a, Tarefa $b): int => $a->getPrazo() <=> $b->getPrazo(),
        });

        $ordenada = new ListaEncadeada();
        foreach ($itens as $item) {
            $ordenada->adicionarNoFim($item);
        }
        return $ordenada;
    }

    // ---------------------------------------------------------------
    // Indicadores
    // ---------------------------------------------------------------

    /** @return array<string, int> */
    private function calcularIndicadores(ListaEncadeada $todas): array
    {
        $indicadores = [
            'total' => 0, 'pendentes' => 0, 'em_andamento' => 0,
            'concluidas' => 0, 'atrasadas' => 0, 'percentual_concluido' => 0,
        ];

        foreach ($todas as $tarefa) {
            $indicadores['total']++;

            if ($tarefa->getStatus() === Tarefa::STATUS_PENDENTE) {
                $indicadores['pendentes']++;
            } elseif ($tarefa->getStatus() === Tarefa::STATUS_EM_ANDAMENTO) {
                $indicadores['em_andamento']++;
            } else {
                $indicadores['concluidas']++;
            }

            if ($tarefa->estaAtrasada()) {
                $indicadores['atrasadas']++;
            }
        }

        $indicadores['percentual_concluido'] = self::percentual($indicadores['concluidas'], $indicadores['total']);
        return $indicadores;
    }

    /**
     * Progresso por disciplina (inclui disciplinas ainda sem tarefas).
     *
     * @param Disciplina[] $disciplinas
     * @return array<int, array{id: int, nome: string, total: int, concluidas: int, percentual: int}>
     */
    private function resumirPorDisciplina(array $disciplinas, ListaEncadeada $todas): array
    {
        $resumo = [];
        foreach ($disciplinas as $disciplina) {
            $id = (int) $disciplina->getIdDisciplina();
            $resumo[$id] = ['id' => $id, 'nome' => $disciplina->getNome(), 'total' => 0, 'concluidas' => 0, 'percentual' => 0];
        }

        foreach ($todas as $tarefa) {
            $id = $tarefa->getIdDisciplina();
            if (!isset($resumo[$id])) {
                continue;
            }
            $resumo[$id]['total']++;
            if ($tarefa->getStatus() === Tarefa::STATUS_CONCLUIDA) {
                $resumo[$id]['concluidas']++;
            }
        }

        foreach ($resumo as &$linha) {
            $linha['percentual'] = self::percentual($linha['concluidas'], $linha['total']);
        }
        unset($linha);

        return array_values($resumo);
    }

    /**
     * As próximas tarefas ainda abertas (as atrasadas aparecem primeiro).
     * A lista de origem já vem ordenada por prazo, então basta pegar as primeiras.
     *
     * @return Tarefa[]
     */
    private function proximosPrazos(ListaEncadeada $todas): array
    {
        $proximos = [];
        foreach ($todas as $tarefa) {
            if ($tarefa->getStatus() === Tarefa::STATUS_CONCLUIDA) {
                continue;
            }
            $proximos[] = $tarefa;
            if (count($proximos) === self::QUANTIDADE_PROXIMOS) {
                break;
            }
        }
        return $proximos;
    }

    // ---------------------------------------------------------------
    // Apoio
    // ---------------------------------------------------------------

    /** Há algum filtro que reduz a lista? (A ordenação não conta: ela não esconde tarefas.) */
    private static function temFiltroAtivo(array $filtros): bool
    {
        return $filtros['q'] !== '' || $filtros['disciplina'] !== null
            || $filtros['status'] !== '' || $filtros['prioridade'] !== '';
    }

    private static function percentual(int $parte, int $total): int
    {
        return $total > 0 ? (int) round($parte / $total * 100) : 0;
    }

    /** @param string[] $permitidos */
    private static function valorPermitido(mixed $valor, array $permitidos, string $padrao): string
    {
        return is_string($valor) && in_array($valor, $permitidos, true) ? $valor : $padrao;
    }
}
