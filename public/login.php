<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/config.php';

$controller = new AutenticacaoController();
$_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->autenticar() : $controller->exibirLogin();
