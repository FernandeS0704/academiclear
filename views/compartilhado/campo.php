<?php
/**
 * Campo de formulário reutilizável.
 * Espera $campo = ['id', 'rotulo', 'tipo', 'valor'?, 'erro'?, 'autocomplete'?, 'atributos'?].
 * 'atributos' é texto fixo escrito nas próprias views (nunca dado do usuário).
 */
$id = (string) $campo['id'];
$erro = $campo['erro'] ?? null;
$ehSenha = $campo['tipo'] === 'password';
?>
<div class="campo<?= $erro ? ' campo--erro' : '' ?>">
    <label for="<?= Visao::e($id) ?>"><?= Visao::e($campo['rotulo']) ?></label>
    <div class="campo__entrada">
        <input
            id="<?= Visao::e($id) ?>"
            name="<?= Visao::e($id) ?>"
            type="<?= Visao::e($campo['tipo']) ?>"
            value="<?= Visao::e($campo['valor'] ?? '') ?>"
            autocomplete="<?= Visao::e($campo['autocomplete'] ?? 'off') ?>"
            required
            <?= $erro ? 'aria-invalid="true" aria-describedby="erro-' . Visao::e($id) . '"' : '' ?>
            <?= $campo['atributos'] ?? '' ?>
        >
        <?php if ($ehSenha): ?>
            <button type="button" class="campo__alternar" data-alvo="<?= Visao::e($id) ?>" aria-label="Mostrar ou ocultar a senha">Mostrar</button>
        <?php endif; ?>
    </div>
    <?php if ($erro): ?>
        <p class="campo__erro" id="erro-<?= Visao::e($id) ?>" role="alert"><?= Visao::e($erro) ?></p>
    <?php endif; ?>
</div>
