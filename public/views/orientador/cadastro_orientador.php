<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Orientador</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style-cadastro.css">
        

</head>

<body>
<!-- MAIN -->
<main class="main">
        <div class="row justify-content-center">

        <div class="col-md-12">

            <div class="login-card fi fi-3">

            <div class="card-header">
                <h2 id="loginTitle" class="page-sub">Cadastro de Orientador</h2>
            </div>

                <div class="card-body">
                    <form action="cadastrar_orientador.php" method="POST">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome</label>
                                <input id="nome" type="text" name="nome" class="form-control" placeholder="Informe seu nome" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input id="email" type="email" name="email" class="form-control" placeholder="Informe seu email" required>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Senha</label>
                                <input id="senha" type="password" name="senha" class="form-control" placeholder="Informe sua senha" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Área de Atuação</label>
                                <select name="atuacao" id="atuacao" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Pesquisa">Pesquisa</option>
                                    <option value="Desenvolvimento WEB">Desenvolvimento WEB</option>
                                    <option value="Desenvolvimento Mobile">Desenvolvimento Mobile</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Titulação</label>
                                <select name="titulacao" id="titulacao" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Doutor">Doutor</option>
                                    <option value="Mestre">Mestre</option>
                                    <option value="Graduado">Graduado</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" id="cadastrarOrientador" class="btn btn-primary">
                                Cadastrar Orientador
                            </button>
                        </div>

                    </form>
                        <div class="d-grid mt-4">
                            <a href="../login.php" class="btn btn-secondary btn-lg">
                            Voltar
                            </a>
                        </div>
                </div>
            </div>
        </div>
    </div>      
</main>
<script type="module" src="../../assets/service/orientador/cadastrar_orientador.js"></script>
</body>
</html>
