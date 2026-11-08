<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Coordenador</title>

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
                <h2 id="loginTitle" class="page-sub">Cadastro de Coordenador</h2>
            </div>

                <div class="card-body">

                    <form action="cadastrar_coordenador.php" method="POST">

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
                                <label class="form-label">Departamento</label>
                                <select name="departamento" id="departamento" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="Politécnico">Politécnico</option>
                                    <option value="Direito">Direito</option>
                                    <option value="Administração">Administração</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Instituição</label>

                                <select id="instituicao" name="instituicao_id" class="form-control" required>
                                    <option value="">Selecione</option>
                                    <option value="1">PUCPR</option>
                                    <option value="2">UTFPR</option>
                                    <option value="3">UFPR</option>
                                </select>

                            </div>

                        </div>

                        <div class="d-grid mt-4">
                            <button type="button" id="cadastrar" class="btn btn-primary">
                                Cadastrar Coordenador
                            </button>
                        </div>

                    </form>
                        <div class="d-grid mt-4">
                            <a href="../login.php" class="btn btn-outline-secondary btn-lg">
                            Voltar
                            </a>
                        </div>
                </div>
            </div>
        </div>
    </div>      
</main>
<script type="module" src="../../assets/service/coordenador/cadastrar_coordenador.js"></script>
</body>
</html>
