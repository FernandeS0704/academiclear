<?php
/** Tela de cadastro. Variáveis: $erros, $erroGeral, $antigo, $flash, $csrf. */
$tituloPagina = 'Criar conta';
$classeCorpo = 'pagina-auth';
require __DIR__ . '/../compartilhado/cabecalho.php';
?>
<main class="auth">
    <section class="cartao">
        <div class="marca"><span class="marca__icone" aria-hidden="true">A</span><span>AcademiClear</span></div>
        <h1>Criar conta</h1>
        <p class="subtitulo">Leva menos de um minuto. Depois é só entrar e organizar suas tarefas.</p>

        <?php require __DIR__ . '/../compartilhado/alertas.php'; ?>

        <form method="post" action="cadastro.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Visao::e($csrf) ?>">

            <?php
            $campo = ['id' => 'nome', 'rotulo' => 'Nome completo', 'tipo' => 'text', 'valor' => $antigo['nome'] ?? '', 'erro' => $erros['nome'] ?? null, 'autocomplete' => 'name', 'atributos' => 'maxlength="100"'];
            require __DIR__ . '/../compartilhado/campo.php';

            $campo = ['id' => 'email', 'rotulo' => 'E-mail', 'tipo' => 'email', 'valor' => $antigo['email'] ?? '', 'erro' => $erros['email'] ?? null, 'autocomplete' => 'email', 'atributos' => 'maxlength="150"'];
            require __DIR__ . '/../compartilhado/campo.php';

            $campo = ['id' => 'senha', 'rotulo' => 'Senha (mínimo ' . AutenticacaoService::SENHA_MINIMA . ' caracteres)', 'tipo' => 'password', 'erro' => $erros['senha'] ?? null, 'autocomplete' => 'new-password', 'atributos' => 'maxlength="72"'];
            require __DIR__ . '/../compartilhado/campo.php';

            $campo = ['id' => 'confirmacao', 'rotulo' => 'Confirmar senha', 'tipo' => 'password', 'erro' => $erros['confirmacao'] ?? null, 'autocomplete' => 'new-password', 'atributos' => 'maxlength="72"'];
            require __DIR__ . '/../compartilhado/campo.php';
            ?>

            <button type="submit" class="botao botao--primario botao--bloco">Criar conta</button>
        </form>

        <p class="auth__rodape">Já tem conta? <a href="login.php">Entrar</a></p>
    </section>
</main>
<?php require __DIR__ . '/../compartilhado/rodape.php'; ?>
