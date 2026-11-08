<?php
/** Página inicial protegida. Variáveis: $usuario (Usuario), $csrf. */
$tituloPagina = 'Dashboard';
$classeCorpo = 'pagina-interna';
require __DIR__ . '/../compartilhado/cabecalho.php';
?>
<header class="topo">
    <div class="topo__conteudo">
        <div class="marca"><span class="marca__icone" aria-hidden="true">A</span><span>AcademiClear</span></div>
        <div class="topo__usuario">
            <span class="topo__nome"><?= Visao::e($usuario->getNome()) ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= Visao::e($csrf) ?>">
                <button type="submit" class="botao botao--secundario">Sair</button>
            </form>
        </div>
    </div>
</header>

<main class="conteudo">
    <h1>Olá, <?= Visao::e(explode(' ', $usuario->getNome())[0]) ?>!</h1>
    <p class="subtitulo">Você está autenticado. Seu painel de disciplinas e tarefas será construído nas próximas etapas.</p>
</main>
<?php require __DIR__ . '/../compartilhado/rodape.php'; ?>
