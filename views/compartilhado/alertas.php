<?php
/** Mensagens de retorno. Usa $flash (['tipo', 'mensagem'] ou null) e $erroGeral (texto ou null). */
?>
<?php if (!empty($flash)): ?>
    <div class="alerta alerta--<?= $flash['tipo'] === 'sucesso' ? 'sucesso' : 'erro' ?>" role="<?= $flash['tipo'] === 'sucesso' ? 'status' : 'alert' ?>">
        <?= Visao::e($flash['mensagem']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($erroGeral)): ?>
    <div class="alerta alerta--erro" role="alert"><?= Visao::e($erroGeral) ?></div>
<?php endif; ?>
