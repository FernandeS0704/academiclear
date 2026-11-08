<?php
declare(strict_types=1);

/**
 * Testes simples dos Models e Repositories (linha de comando, sem interface visual).
 *
 * Como executar (com o MySQL do XAMPP ligado e o banco importado):
 *   C:\xampp\php\php.exe tests\testar_repositorios.php
 *
 * O teste cria usuários com e-mail "teste.<código>@academiclear.test" e
 * REMOVE tudo ao final (mesmo se algum teste falhar). Seus dados reais
 * não são tocados.
 */

require_once __DIR__ . '/../app/config/config.php';

/** Contadores globais do teste. */
$GLOBALS['testes_ok'] = 0;
$GLOBALS['testes_falhas'] = 0;

function verificar(string $descricao, bool $condicao): void
{
    if ($condicao) {
        $GLOBALS['testes_ok']++;
        echo "  [OK]    {$descricao}" . PHP_EOL;
    } else {
        $GLOBALS['testes_falhas']++;
        echo "  [FALHA] {$descricao}" . PHP_EOL;
    }
}

/** Confirma que a função lança a exceção esperada. */
function verificarExcecao(string $descricao, string $classe, callable $acao): void
{
    try {
        $acao();
        verificar($descricao . " (nenhuma exceção lançada)", false);
    } catch (Throwable $e) {
        verificar($descricao, $e instanceof $classe);
    }
}

function secao(string $titulo): void
{
    echo PHP_EOL . "== {$titulo} ==" . PHP_EOL;
}

/**
 * Executa todos os testes usando a conexão informada.
 * @return int quantidade de falhas
 */
function executarTestes(PDO $pdo): int
{
    $usuarios = new UsuarioRepository($pdo);
    $disciplinas = new DisciplinaRepository($pdo);
    $tarefas = new TarefaRepository($pdo, $disciplinas);

    $codigo = bin2hex(random_bytes(4));
    $emailA = "teste.a.{$codigo}@academiclear.test";
    $emailB = "teste.b.{$codigo}@academiclear.test";
    $idsParaLimpar = [];
    $prazo = date('Y-m-d', strtotime('+7 days'));

    try {
        // 1. Cadastro de usuário
        secao('1. Cadastro de usuário');
        $ana = $usuarios->cadastrar(Usuario::novo('Ana Teste', $emailA, 'senha123'));
        $idsParaLimpar[] = $ana->getIdUsuario();
        verificar('usuário recebeu ID', $ana->getIdUsuario() > 0);
        verificar('senha NÃO está em texto puro', $ana->getSenha() !== 'senha123');

        $bruno = $usuarios->cadastrar(Usuario::novo('Bruno Teste', $emailB, 'outrasenha'));
        $idsParaLimpar[] = $bruno->getIdUsuario();

        verificarExcecao('e-mail repetido é rejeitado', RegistroDuplicadoException::class, function () use ($usuarios, $emailA) {
            $usuarios->cadastrar(Usuario::novo('Duplicado', $emailA, 'senha123'));
        });

        // 2. Busca de usuário
        secao('2. Busca de usuário por ID e e-mail');
        $porId = $usuarios->buscarPorId($ana->getIdUsuario());
        verificar('buscarPorId encontra o usuário', $porId !== null && $porId->getNome() === 'Ana Teste');
        $porEmail = $usuarios->buscarPorEmail(strtoupper($emailA));
        verificar('buscarPorEmail encontra (ignora maiúsculas)', $porEmail !== null && $porEmail->getIdUsuario() === $ana->getIdUsuario());
        verificar('password_verify aceita a senha correta', $porEmail->verificarSenha('senha123'));
        verificar('password_verify recusa senha errada', !$porEmail->verificarSenha('errada'));
        verificar('buscarPorId inexistente devolve null', $usuarios->buscarPorId(2147483000) === null);
        verificar('listarTodos inclui os usuários de teste', count($usuarios->listarTodos()) >= 2);

        // 3. Cadastro de disciplina
        secao('3. Cadastro de disciplina');
        $calculo = $disciplinas->cadastrar(new Disciplina(null, 'Cálculo', $ana->getIdUsuario()));
        $qualidade = $disciplinas->cadastrar(new Disciplina(null, 'Qualidade de Software', $ana->getIdUsuario()));
        $discBruno = $disciplinas->cadastrar(new Disciplina(null, 'Banco de Dados', $bruno->getIdUsuario()));
        verificar('disciplina recebeu ID', $calculo->getIdDisciplina() > 0);
        verificarExcecao('disciplina com nome repetido para o mesmo usuário é rejeitada', RegistroDuplicadoException::class, function () use ($disciplinas, $ana) {
            $disciplinas->cadastrar(new Disciplina(null, 'Cálculo', $ana->getIdUsuario()));
        });

        // 4. Listagem de disciplinas
        secao('4. Listagem de disciplinas por usuário');
        $listaAna = $disciplinas->listarPorUsuario($ana->getIdUsuario());
        verificar('Ana vê exatamente 2 disciplinas', count($listaAna) === 2);
        verificar('Bruno vê somente a sua', count($disciplinas->listarPorUsuario($bruno->getIdUsuario())) === 1);
        verificar('Bruno não consegue buscar disciplina da Ana', $disciplinas->buscarPorId($calculo->getIdDisciplina(), $bruno->getIdUsuario()) === null);

        // 5. Cadastro de tarefa
        secao('5. Cadastro de tarefa');
        $t1 = $tarefas->cadastrar(new Tarefa(null, 'Lista 1', 'Exercícios 1 a 10', $prazo, Tarefa::PRIORIDADE_ALTA, Tarefa::STATUS_PENDENTE, null, $calculo->getIdDisciplina(), $ana->getIdUsuario()));
        $t2 = $tarefas->cadastrar(new Tarefa(null, 'Relatório', null, date('Y-m-d', strtotime('+3 days')), Tarefa::PRIORIDADE_MEDIA, Tarefa::STATUS_PENDENTE, null, $qualidade->getIdDisciplina(), $ana->getIdUsuario()));
        verificar('tarefa recebeu ID e data de criação', $t1->getIdTarefa() > 0 && $t1->getDataCriacao() !== null);
        verificarExcecao('não cadastra tarefa em disciplina de outro usuário', InvalidArgumentException::class, function () use ($tarefas, $prazo, $discBruno, $ana) {
            $tarefas->cadastrar(new Tarefa(null, 'Invasora', null, $prazo, Tarefa::PRIORIDADE_BAIXA, Tarefa::STATUS_PENDENTE, null, $discBruno->getIdDisciplina(), $ana->getIdUsuario()));
        });
        verificarExcecao('prioridade inválida é rejeitada pelo model', InvalidArgumentException::class, function () use ($prazo) {
            new Tarefa(null, 'X', null, $prazo, 'urgente', Tarefa::STATUS_PENDENTE, null, 1, 1);
        });

        // 6. Listagem de tarefas
        secao('6. Listagem de tarefas');
        $lista = $tarefas->listarPorUsuario($ana->getIdUsuario());
        verificar('lista encadeada tem 2 tarefas', count($lista) === 2);
        verificar('ordenada por prazo (Relatório vem primeiro)', $lista->primeiro()->getTitulo() === 'Relatório');
        verificar('listarPorDisciplina devolve só as da disciplina', count($tarefas->listarPorDisciplina($calculo->getIdDisciplina(), $ana->getIdUsuario())) === 1);
        verificar('Bruno não vê tarefas da Ana', count($tarefas->listarPorUsuario($bruno->getIdUsuario())) === 0);
        $mapa = $tarefas->construirMapa($ana->getIdUsuario());
        verificar('mapa localiza tarefa pelo ID', $mapa->buscar($t1->getIdTarefa())?->getTitulo() === 'Lista 1');
        verificar('Bruno não busca tarefa da Ana', $tarefas->buscarPorId($t1->getIdTarefa(), $bruno->getIdUsuario()) === null);

        // 7. Edição e exclusão
        secao('7. Edição e exclusão');
        $ana->setNome('Ana Editada');
        $ana->alterarSenha('novaSenha456');
        verificar('atualizar usuário', $usuarios->atualizar($ana));
        $recarregada = $usuarios->buscarPorId($ana->getIdUsuario());
        verificar('nome e senha foram alterados', $recarregada->getNome() === 'Ana Editada' && $recarregada->verificarSenha('novaSenha456'));

        $calculo->setNome('Cálculo I');
        verificar('atualizar disciplina', $disciplinas->atualizar($calculo));
        verificar('disciplina editada persistiu', $disciplinas->buscarPorId($calculo->getIdDisciplina(), $ana->getIdUsuario())->getNome() === 'Cálculo I');
        $usurpadora = new Disciplina($calculo->getIdDisciplina(), 'Hackeada', $bruno->getIdUsuario());
        verificar('Bruno não consegue editar disciplina da Ana', $disciplinas->atualizar($usurpadora) === false);

        $t1->setStatus(Tarefa::STATUS_EM_ANDAMENTO);
        $t1->setTitulo('Lista 1 (revisada)');
        verificar('atualizar tarefa', $tarefas->atualizar($t1));
        $t1Recarregada = $tarefas->buscarPorId($t1->getIdTarefa(), $ana->getIdUsuario());
        verificar('tarefa editada persistiu', $t1Recarregada->getStatus() === Tarefa::STATUS_EM_ANDAMENTO && $t1Recarregada->getTitulo() === 'Lista 1 (revisada)');
        verificar('atualizar sem alterar nada ainda retorna true', $tarefas->atualizar($t1Recarregada));

        verificar('Bruno não consegue excluir tarefa da Ana', $tarefas->excluir($t1->getIdTarefa(), $bruno->getIdUsuario()) === false);
        verificar('excluir tarefa', $tarefas->excluir($t2->getIdTarefa(), $ana->getIdUsuario()));
        verificar('tarefa excluída não é mais encontrada', $tarefas->buscarPorId($t2->getIdTarefa(), $ana->getIdUsuario()) === null);

        verificar('excluir disciplina', $disciplinas->excluir($qualidade->getIdDisciplina(), $ana->getIdUsuario()));
        verificar('excluir disciplina apaga suas tarefas (cascata)', count($tarefas->listarPorDisciplina($qualidade->getIdDisciplina(), $ana->getIdUsuario())) === 0);

        verificar('excluir usuário', $usuarios->excluir($bruno->getIdUsuario()));
        verificar('usuário excluído não é mais encontrado', $usuarios->buscarPorId($bruno->getIdUsuario()) === null);
    } finally {
        // Limpeza: apaga os usuários de teste (disciplinas e tarefas saem por cascata).
        foreach ($idsParaLimpar as $id) {
            $usuarios->excluir($id);
        }
    }

    return $GLOBALS['testes_falhas'];
}

// Executa automaticamente apenas quando o arquivo é chamado direto pelo terminal.
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    try {
        $falhas = executarTestes(Conexao::obter());
    } catch (Throwable $e) {
        echo PHP_EOL . 'ERRO: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }

    echo PHP_EOL . "Resultado: {$GLOBALS['testes_ok']} OK, {$falhas} falha(s)." . PHP_EOL;
    exit($falhas === 0 ? 0 : 1);
}
