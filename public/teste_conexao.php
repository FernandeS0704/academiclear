<?php
declare(strict_types=1);

/**
 * Ponto de entrada da aplicação.
 * Nesta primeira etapa serve apenas para confirmar que a instalação está correta.
 * O roteamento, login e dashboard serão implementados nas próximas etapas.
 */

require_once __DIR__ . '/../app/config/config.php';

$conectado = false;
$mensagem = '';

try {
    Conexao::obter()->query('SELECT 1');
    $conectado = true;
    $mensagem = 'Conexão com o banco de dados estabelecida com sucesso.';
} catch (Throwable $e) {
    $mensagem = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(NOME_APLICACAO) ?></title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 40rem; margin: 3rem auto; padding: 0 1rem; line-height: 1.5; }
        .ok { color: #137333; }
        .erro { color: #b3261e; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars(NOME_APLICACAO) ?></h1>
    <p>Estrutura inicial instalada.</p>
    <p class="<?= $conectado ? 'ok' : 'erro' ?>"><?= htmlspecialchars($mensagem) ?></p>
</body>
</html>
