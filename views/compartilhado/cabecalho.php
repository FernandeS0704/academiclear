<?php
/** Início da página HTML. Espera $tituloPagina e, opcionalmente, $classeCorpo. */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Visao::e(($tituloPagina ?? 'Início') . ' · ' . NOME_APLICACAO) ?></title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="<?= Visao::e($classeCorpo ?? '') ?>">
