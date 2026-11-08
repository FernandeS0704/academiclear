<?php
/** Tela de login. Variáveis: $erroGeral, $antigo, $flash, $csrf. */
$tituloPagina = 'Entrar';
$classeCorpo = 'pagina-auth';
require __DIR__ . '/../compartilhado/cabecalho.php';
?>
<main class="auth">
    <section class="cartao">
        <div class="marca"><span class="marca__icone" aria-hidden="true">A</span><span>AcademiClear</span></div>
        <h1>Entrar</h1>
        <p class="subtitulo">Acesse sua conta para organizar suas atividades acadêmicas.</p>

        <?php require __DIR__ . '/../compartilhado/alertas.php'; ?>

        <form method="post" action="login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Visao::e($csrf) ?>">

            <?php
            $campo = ['id' => 'email', 'rotulo' => 'E-mail', 'tipo' => 'email', 'valor' => $antigo['email'] ?? '', 'autocomplete' => 'email', 'atributos' => 'maxlength="150"'];
            require __DIR__ . '/../compartilhado/campo.php';

            $campo = ['id' => 'senha', 'rotulo' => 'Senha', 'tipo' => 'password', 'autocomplete' => 'current-password', 'atributos' => 'maxlength="72"'];
            require __DIR__ . '/../compartilhado/campo.php';
            ?>

            <button type="submit" class="botao botao--primario botao--bloco">Entrar</button>
        </form>

        <p class="auth__rodape">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </section>
</main>
<?php require __DIR__ . '/../compartilhado/rodape.php'; ?>
