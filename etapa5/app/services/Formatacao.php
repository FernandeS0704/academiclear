<?php
declare(strict_types=1);

/**
 * Apoio às views: transforma valores técnicos (status, prioridade, datas)
 * em textos em português. Não acessa banco nem sessão.
 *
 * Uso na view:  <?= Visao::e(Formatacao::status($tarefa->getStatus())) ?>
 */
class Formatacao
{
    /**
     * "atrasada" não é um status gravado no banco: é um estado calculado
     * (prazo vencido e tarefa não concluída), usado nos filtros e indicadores.
     */
    public const STATUS_ATRASADA = 'atrasada';

    private const ROTULOS_STATUS = [
        Tarefa::STATUS_PENDENTE     => 'Pendente',
        Tarefa::STATUS_EM_ANDAMENTO => 'Em andamento',
        Tarefa::STATUS_CONCLUIDA    => 'Concluída',
        self::STATUS_ATRASADA       => 'Atrasada',
    ];

    private const ROTULOS_PRIORIDADE = [
        Tarefa::PRIORIDADE_ALTA  => 'Alta',
        Tarefa::PRIORIDADE_MEDIA => 'Média',
        Tarefa::PRIORIDADE_BAIXA => 'Baixa',
    ];

    public static function status(string $status): string
    {
        return self::ROTULOS_STATUS[$status] ?? $status;
    }

    public static function prioridade(string $prioridade): string
    {
        return self::ROTULOS_PRIORIDADE[$prioridade] ?? $prioridade;
    }

    /** Converte AAAA-MM-DD em DD/MM/AAAA. Se o formato for inesperado, devolve o texto original. */
    public static function data(string $data): string
    {
        $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        return $objeto === false ? $data : $objeto->format('d/m/Y');
    }

    /** "1 tarefa", "3 tarefas". */
    public static function quantidade(int $numero, string $singular, string $plural): string
    {
        return $numero . ' ' . ($numero === 1 ? $singular : $plural);
    }

    /**
     * Situação do prazo em relação a hoje, para tarefas ainda abertas.
     * Ex.: "Atrasada há 3 dias", "Vence hoje", "Vence amanhã", "Vence em 5 dias".
     * Tarefas concluídas não têm situação de prazo (devolve texto vazio).
     */
    public static function situacaoPrazo(Tarefa $tarefa): string
    {
        if ($tarefa->getStatus() === Tarefa::STATUS_CONCLUIDA) {
            return '';
        }

        $dias = self::diasAteOPrazo($tarefa->getPrazo());

        return match (true) {
            $dias < 0   => 'Atrasada há ' . self::quantidade(-$dias, 'dia', 'dias'),
            $dias === 0 => 'Vence hoje',
            $dias === 1 => 'Vence amanhã',
            default     => 'Vence em ' . $dias . ' dias',
        };
    }

    /** Classe CSS da situação do prazo: "atrasada", "hoje" ou "" (sem destaque). */
    public static function classePrazo(Tarefa $tarefa): string
    {
        if ($tarefa->getStatus() === Tarefa::STATUS_CONCLUIDA) {
            return '';
        }

        $dias = self::diasAteOPrazo($tarefa->getPrazo());
        return $dias < 0 ? 'atrasada' : ($dias === 0 ? 'hoje' : '');
    }

    /** Texto curto de uma descrição longa: junta quebras de linha e corta com reticências. */
    public static function resumo(?string $texto, int $limite = 140): string
    {
        if ($texto === null) {
            return '';
        }

        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));
        return mb_strimwidth($texto, 0, $limite, '…', 'UTF-8');
    }

    /** Dias entre hoje e o prazo (negativo se o prazo já passou). */
    private static function diasAteOPrazo(string $prazo): int
    {
        $hoje = new DateTimeImmutable('today');
        $alvo = DateTimeImmutable::createFromFormat('!Y-m-d', $prazo);

        return $alvo === false ? 0 : (int) $hoje->diff($alvo)->format('%r%a');
    }
}
