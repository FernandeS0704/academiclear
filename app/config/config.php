<?php
declare(strict_types=1);

/**
 * Configuração geral do projeto.
 *
 * Deve ser incluído (require) no início de qualquer ponto de entrada,
 * como public/index.php. Define constantes, fuso horário e o autoload
 * de classes, para que não seja preciso dar require em cada arquivo.
 */

define('RAIZ_PROJETO', dirname(__DIR__, 2));
define('NOME_APLICACAO', 'AcademiClear');

date_default_timezone_set('America/Sao_Paulo');

// Em desenvolvimento exibimos os erros; em produção isso deve ser desligado.
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Autoload simples (sem Composer): procura a classe nas pastas do projeto.
 * Convenção: um arquivo por classe, com o mesmo nome da classe.
 */
spl_autoload_register(function (string $classe): void {
    $pastas = [
        'config',
        'models',
        'repositories',
        'services',
        'services/estruturas',
        'controllers',
    ];

    foreach ($pastas as $pasta) {
        $arquivo = RAIZ_PROJETO . '/app/' . $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});
