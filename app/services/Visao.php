<?php
declare(strict_types=1);

/**
 * Renderização de views (arquivos em /views) e escape de saída.
 *
 * Uso no controller:  Visao::renderizar('login/login', ['erros' => $erros]);
 * Uso na view:        <?= Visao::e($valor) ?>
 */
class Visao
{
    /** @param array<string, mixed> $dados variáveis disponíveis dentro da view */
    public static function renderizar(string $visao, array $dados = []): void
    {
        $arquivo = RAIZ_PROJETO . '/views/' . $visao . '.php';

        if (!is_file($arquivo)) {
            throw new RuntimeException("View não encontrada: {$visao}");
        }

        extract($dados, EXTR_SKIP);
        require $arquivo;
    }

    /** Escapa texto para HTML (proteção contra XSS). Use em TODA saída vinda do usuário. */
    public static function e(?string $texto): string
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
