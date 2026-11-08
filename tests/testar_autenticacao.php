<?php
declare(strict_types=1);

/**
 * Testes do AutenticacaoService (cadastro e login), sem navegador.
 *
 * Como executar (com o MySQL do XAMPP ligado e o banco importado):
 *   C:\xampp\php\php.exe tests\testar_autenticacao.php
 *
 * Cria usuários "teste.auth.<código>@academiclear.test" e remove tudo ao final.
 * Os cenários que dependem de navegador (sessão, redirecionamentos, logout)
 * estão no roteiro manual do README.
 */

require_once __DIR__ . '/testar_repositorios.php'; // reaproveita verificar(), verificarExcecao() e secao()

/** @return int quantidade de falhas */
function executarTestesAutenticacao(PDO $pdo): int
{
    $usuarios = new UsuarioRepository($pdo);
    $servico = new AutenticacaoService($usuarios);

    $email = 'teste.auth.' . bin2hex(random_bytes(4)) . '@academiclear.test';
    $idsParaLimpar = [];

    /** Captura os erros de validação de um cadastro. */
    $errosDe = function (string $nome, string $email, string $senha, string $conf) use ($servico): array {
        try {
            $servico->cadastrar($nome, $email, $senha, $conf);
            return [];
        } catch (ValidacaoException $e) {
            return $e->getErros();
        }
    };

    try {
        secao('1. Cadastro com dados válidos');
        $usuario = $servico->cadastrar('  Maria Teste  ', strtoupper($email), 'senhaSegura1', 'senhaSegura1');
        $idsParaLimpar[] = $usuario->getIdUsuario();
        verificar('usuário cadastrado com ID', $usuario->getIdUsuario() > 0);
        verificar('nome foi limpo (trim)', $usuario->getNome() === 'Maria Teste');
        verificar('e-mail foi normalizado para minúsculas', $usuario->getEmail() === $email);

        $salvo = $usuarios->buscarPorEmail($email);
        verificar('senha gravada NÃO está em texto puro', $salvo !== null && $salvo->getSenha() !== 'senhaSegura1');
        verificar('senha gravada é um hash válido do password_hash()', $salvo !== null && password_get_info($salvo->getSenha())['algo'] !== null);

        secao('2. E-mail já existente');
        $erros = $errosDe('Outra Pessoa', $email, 'senhaSegura1', 'senhaSegura1');
        verificar('cadastro duplicado é recusado com erro no campo e-mail', isset($erros['email']));
        $erros = $errosDe('Outra Pessoa', strtoupper($email), 'senhaSegura1', 'senhaSegura1');
        verificar('duplicidade ignora maiúsculas/minúsculas', isset($erros['email']));

        secao('3. Senhas diferentes e demais validações');
        $erros = $errosDe('Fulano', 'fulano.' . $email, 'senhaSegura1', 'senhaDiferente');
        verificar('senhas diferentes: erro em "confirmacao"', ($erros['confirmacao'] ?? '') === 'As senhas não conferem.');
        verificar('campos vazios: erro em todos os campos', count($errosDe('', '', '', '')) === 4);
        verificar('e-mail inválido é recusado', isset($errosDe('Fulano', 'nao-e-email', 'senhaSegura1', 'senhaSegura1')['email']));
        verificar('senha curta é recusada', isset($errosDe('Fulano', 'f.' . $email, '1234567', '1234567')['senha']));
        verificar('nenhum usuário foi criado nesses cenários', count(array_filter(
            $usuarios->listarTodos(),
            fn(Usuario $u) => str_contains($u->getEmail(), 'teste.auth.') && $u->getIdUsuario() !== $usuario->getIdUsuario()
        )) === 0);

        secao('4. Login com credenciais corretas');
        $logado = $servico->autenticar($email, 'senhaSegura1');
        verificar('login válido devolve o usuário', $logado !== null && $logado->getIdUsuario() === $usuario->getIdUsuario());
        verificar('login ignora maiúsculas no e-mail', $servico->autenticar(strtoupper($email), 'senhaSegura1') !== null);

        secao('5. Login com credenciais incorretas');
        verificar('senha incorreta devolve null', $servico->autenticar($email, 'senhaErrada') === null);
        verificar('e-mail inexistente devolve null (mesmo resultado)', $servico->autenticar('ninguem@academiclear.test', 'senhaSegura1') === null);
        verificar('e-mail vazio devolve null', $servico->autenticar('', 'senhaSegura1') === null);
    } finally {
        foreach ($idsParaLimpar as $id) {
            $usuarios->excluir($id);
        }
    }

    return $GLOBALS['testes_falhas'];
}

// Executa automaticamente apenas quando chamado direto pelo terminal.
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    try {
        $falhas = executarTestesAutenticacao(Conexao::obter());
    } catch (Throwable $e) {
        echo PHP_EOL . 'ERRO: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }

    echo PHP_EOL . "Resultado: {$GLOBALS['testes_ok']} OK, {$falhas} falha(s)." . PHP_EOL;
    exit($falhas === 0 ? 0 : 1);
}
