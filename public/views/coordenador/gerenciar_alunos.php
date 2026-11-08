<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['usuario_tipo'] != 'coordenador') {
    header("Location: ../login.php");
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <title>Gradly — Dashboard do Coordenador</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../../assets/css/style-coordenador.css">

</head>

<body>

<!-- TOPBAR -->
<header class="topbar">

  <a class="topbar-brand" href="#">
    Gradly
  </a>

  <div class="topbar-right">

    <button class="icon-btn">
      <svg viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
    </button>

    <button class="icon-btn">
      <svg viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
    </button>

      <a href="perfil.php?id=<?php echo $_SESSION['usuario_id'];?>" style="text-decoration:none;color:inherit;">
        <span style="text-transform:capitalize">
          <?php echo $_SESSION['usuario_nome']; ?>
        </span>
      </a>

    <button class="icon-btn" id="logout">
      <svg viewBox="0 0 24 24">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </button>

  </div>

</header>

<!-- SIDEBAR -->
<aside class="sidebar">

  <div class="sec-label">
    <div class="sec-label-inner">
      <svg class="si" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>

      Início
    </div>
  </div>

  <ul class="snav">
    <li>
      <a href="dashboard_coordenador.php">
        Dashboard
      </a>
    </li>
  </ul>

  <div class="sec-label" style="margin-top:.5rem;">

    <div class="sec-label-inner">

      <svg class="si" viewBox="0 0 24 24">
        <ellipse cx="12" cy="5" rx="9" ry="3"/>
        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
      </svg>

      Sistema

    </div>

  </div>

  <ul class="snav">
    <li><a href="gerenciar_alunos.php" class="active">Alunos</a></li>
    <li><a href="gerenciar_orientadores.php">Orientadores</a></li>
    <li><a href="#">Projetos</a></li>
  </ul>

</aside>

<!-- MAIN -->
<main class="main">

  <div class="content">

    <div class="content-main">
        <h1>Gerenciar Alunos</h1>
    
        <div class="card">
            <div class="card-header">
            Lista de Alunos
            </div>
            <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Projeto</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody id="alunos-table-body">

                </tbody>
            </table>
            </div>
        </div>

    </div>

  </div>

</main>

<script src="../../assets/service/controle/logout.js"></script>
<script type="module" src="../../assets/service/coordenador/buscar_alunos.js"></script>

</body>
</html>