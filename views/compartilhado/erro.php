<?php
/** Tela de erro genérica. Espera $mensagem. */
$tituloPagina = 'Erro';
$classeCorpo = 'pagina-auth';
require __DIR__ . '/cabecalho.php';
?>
<main class="auth">
    <section class="cartao">
        <h1>Ops, algo deu errado</h1>
        <div class="alerta alerta--erro" role="alert"><?= Visao::e($mensagem) ?></div>
        <p class="auth__rodape"><a href="login.php">Voltar ao login</a></p>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
