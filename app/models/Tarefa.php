<?php
declare(strict_types=1);

/**
 * Model Tarefa: representa um registro da tabela "tarefa".
 *
 * Os valores válidos de prioridade e status ficam em constantes para
 * serem reutilizados por formulários, filtros e validações.
 */
class Tarefa
{
    public const PRIORIDADE_ALTA  = 'alta';
    public const PRIORIDADE_MEDIA = 'media';
    public const PRIORIDADE_BAIXA = 'baixa';

    public const STATUS_PENDENTE     = 'pendente';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_CONCLUIDA    = 'concluida';

    public const PRIORIDADES = [self::PRIORIDADE_ALTA, self::PRIORIDADE_MEDIA, self::PRIORIDADE_BAIXA];
    public const STATUS      = [self::STATUS_PENDENTE, self::STATUS_EM_ANDAMENTO, self::STATUS_CONCLUIDA];

    private ?int $idTarefa;
    private string $titulo;
    private ?string $descricao;
    private string $prazo; // formato AAAA-MM-DD
    private string $prioridade;
    private string $status;
    private ?string $dataCriacao;
    private int $idDisciplina;
    private int $idUsuario;

    public function __construct(
        ?int $idTarefa,
        string $titulo,
        ?string $descricao,
        string $prazo,
        string $prioridade,
        string $status,
        ?string $dataCriacao,
        int $idDisciplina,
        int $idUsuario
    ) {
        $this->idTarefa = $idTarefa;
        $this->setTitulo($titulo);
        $this->setDescricao($descricao);
        $this->setPrazo($prazo);
        $this->setPrioridade($prioridade);
        $this->setStatus($status);
        $this->dataCriacao = $dataCriacao;
        $this->idDisciplina = $idDisciplina;
        $this->idUsuario = $idUsuario;
    }

    public static function deArray(array $linha): self
    {
        return new self(
            (int) $linha['id_tarefa'],
            $linha['titulo'],
            $linha['descricao'],
            $linha['prazo'],
            $linha['prioridade'],
            $linha['status'],
            $linha['data_criacao'],
            (int) $linha['id_disciplina'],
            (int) $linha['id_usuario']
        );
    }

    /** Indica se a tarefa está atrasada (prazo anterior a hoje e ainda não concluída). */
    public function estaAtrasada(): bool
    {
        return $this->status !== self::STATUS_CONCLUIDA && $this->prazo < date('Y-m-d');
    }

    public function getIdTarefa(): ?int
    {
        return $this->idTarefa;
    }

    public function setIdTarefa(int $idTarefa): void
    {
        $this->idTarefa = $idTarefa;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        $titulo = trim($titulo);
        if ($titulo === '') {
            throw new InvalidArgumentException('O título da tarefa é obrigatório.');
        }
        $this->titulo = $titulo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void
    {
        $descricao = $descricao === null ? null : trim($descricao);
        $this->descricao = ($descricao === '') ? null : $descricao;
    }

    public function getPrazo(): string
    {
        return $this->prazo;
    }

    public function setPrazo(string $prazo): void
    {
        $data = DateTime::createFromFormat('Y-m-d', $prazo);
        if ($data === false || $data->format('Y-m-d') !== $prazo) {
            throw new InvalidArgumentException('Prazo inválido. Use o formato AAAA-MM-DD.');
        }
        $this->prazo = $prazo;
    }

    public function getPrioridade(): string
    {
        return $this->prioridade;
    }

    public function setPrioridade(string $prioridade): void
    {
        if (!in_array($prioridade, self::PRIORIDADES, true)) {
            throw new InvalidArgumentException('Prioridade inválida.');
        }
        $this->prioridade = $prioridade;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, self::STATUS, true)) {
            throw new InvalidArgumentException('Status inválido.');
        }
        $this->status = $status;
    }

    public function getDataCriacao(): ?string
    {
        return $this->dataCriacao;
    }

    /** Usado pelo repository após o cadastro, com o valor gerado pelo banco. */
    public function setDataCriacao(string $dataCriacao): void
    {
        $this->dataCriacao = $dataCriacao;
    }

    public function getIdDisciplina(): int
    {
        return $this->idDisciplina;
    }

    public function setIdDisciplina(int $idDisciplina): void
    {
        $this->idDisciplina = $idDisciplina;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }
}
