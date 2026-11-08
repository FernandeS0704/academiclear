<?php
declare(strict_types=1);

/**
 * Modelo de configuração local.
 *
 * COMO USAR:
 *   1. Copie este arquivo para "config.local.php" (na mesma pasta).
 *   2. Ajuste os valores para o seu ambiente.
 *
 * O arquivo config.local.php está no .gitignore e NÃO vai para o GitHub.
 * Este arquivo (config.exemplo.php) é versionado e não deve conter senhas reais.
 */

return [
    'banco' => [
        'host'     => 'localhost',
        'porta'    => 3306,
        'nome'     => 'academiclear',
        'usuario'  => 'root',
        'senha'    => '',          // XAMPP: senha do root é vazia por padrão
        'charset'  => 'utf8mb4',
    ],
];
