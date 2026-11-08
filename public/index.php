<?php
declare(strict_types=1);

/** Ponto de entrada: envia o usuário para o dashboard (logado) ou para o login. */

require_once __DIR__ . '/../app/config/config.php';

header('Location: ' . (Sessao::estaAutenticado() ? 'dashboard.php' : 'login.php'));
exit;
