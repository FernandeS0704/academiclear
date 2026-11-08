<?php
declare(strict_types=1);

/**
 * Base dos controllers: utilidades comuns (redirecionamento e leitura de formulários).
 */
abstract class ControllerBase
{
    /** Redireciona e encerra a execução. Os destinos são relativos à pasta public/. */
    protected static function redirecionar(string $destino): never
    {
        header('Location: ' . $destino);
        exit;
    }

    protected function ehPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /** Lê um campo de $_POST como texto (ignora valores em formato de array). */
    protected function entrada(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? $valor : '';
    }

    protected function tokenCsrfValido(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        return Sessao::validarCsrf(is_string($token) ? $token : null);
    }
}
