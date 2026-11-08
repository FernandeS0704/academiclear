<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../login.php");
  exit;
}

if ($_SESSION['usuario_tipo'] != 'aluno') {
  header("Location: ../login.php");
  exit;
}

$idPerfil = $_GET['id'] ?? $_SESSION['usuario_id'];
if ((int) $idPerfil !== (int) $_SESSION['usuario_id']) {
  header("Location: perfil.php?id=" . $_SESSION['usuario_id']);
  exit;
}
?>

<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <title>Gradly — Perfil do Aluno</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../assets/css/style-coordenador.css">

  <style>
    .content-main .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      overflow: hidden;
      margin-top: 1.25rem;
    }

    #perfil-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0;
      font-size: 13.5px;
      color: var(--text-1);
    }

    #perfil-table td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border);
      transition: background-color 0.12s ease;
    }

    #perfil-table tr:last-child td {
      border-bottom: none;
    }

    #perfil-table td:first-child {
      font-weight: 600;
      color: var(--text-2);
      width: 25%;
      background-color: var(--surface2);
      border-right: 1px solid var(--border);
    }

    #perfil-table td:last-child {
      font-weight: 400;
      color: var(--text-1);
    }

    #perfil-table tr:hover td {
      background-color: var(--green-light);
    }

    #perfil-table tr:hover td:first-child {
      color: var(--green-text);
    }

    .card-actions-container {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      padding: 1.5rem 1.25rem;
      background: var(--surface);
    }

    .btn-full-width {
      width: 100%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 14px !important;
      font-size: 14px !important;
      text-decoration: none;
      box-sizing: border-box;
    }

    a.btn-outline:hover {
      color: var(--green) !important;
    }

    .modal-content {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .modal-header {
      border-bottom: 1px solid var(--border);
      padding: 1.25rem 1.5rem;
    }

    .modal-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--text-1);
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-footer {
      border-top: 1px solid var(--border);
      padding: 1rem 1.5rem;
      gap: 8px;
    }

    .form-label {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: var(--text-2);
      margin-bottom: 0.4rem;
    }

    .form-control,
    .form-select {
      font-family: inherit;
      font-size: 13.5px;
      color: var(--text-1);
      background-color: var(--bg);
      border: 1px solid var(--border-mid);
      border-radius: var(--r-md);
      padding: 0.55rem 0.75rem;
      transition: all 0.15s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
      background-color: var(--surface);
      border-color: var(--green);
      color: var(--text-1);
      box-shadow: 0 0 0 3px var(--green-light);
      outline: none;
    }

    .form-select {
      cursor: pointer;
    }
  </style>

</head>

<body>

  <header class="topbar">
    <a class="topbar-brand" href="dashboard_aluno.php">Gradly</a>
    <div class="topbar-right">
      <button class="icon-btn">
        <svg viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" />
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
          <line x1="12" y1="17" x2="12.01" y2="17" />
        </svg>
      </button>
      <button class="icon-btn">
        <svg viewBox="0 0 24 24">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
          <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
      </button>
      <a href="perfil.php?id=<?php echo $_SESSION['usuario_id']; ?>" style="text-decoration:none;color:inherit;">
        <span style="text-transform:capitalize"><?php echo $_SESSION['usuario_nome']; ?></span>
      </a>
      <button class="icon-btn" id="logout">
        <svg viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <polyline points="16 17 21 12 16 7" />
          <line x1="21" y1="12" x2="9" y2="12" />
        </svg>
      </button>
    </div>
  </header>

  <aside class="sidebar">
    <div class="sec-label">
      <div class="sec-label-inner">
        <svg class="si" viewBox="0 0 24 24">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        Início
      </div>
    </div>
    <ul class="snav">
<li><a href="dashboard_aluno.php" class="active">Dashboard</a></li>
    </ul>
  </aside>

  <main class="main">
    <div class="content">
      <div class="content-main">
        <h1 class="page-title fi fi-2">Perfil do Aluno</h1>

        <div class="card">
          <table id="perfil-table"></table>
          <div class="card-actions-container">
            <button class="btn-primary btn-full-width" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">Editar
              Perfil</button>
            <a href="dashboard_aluno.php"
              class="btn-outline btn-full-width text-center justify-content-center">Voltar</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditarPerfilLabel">Editar Perfil do Aluno</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="form-editar-perfil">
          <div class="modal-body">
            <div class="mb-3">
              <label for="input-nome" class="form-label">Nome Completo</label>
              <input type="text" class="form-control" id="input-nome" name="nome" required>
            </div>
            <div class="mb-3">
              <label for="input-email" class="form-label">E-mail</label>
              <input type="email" class="form-control" id="input-email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="input-matricula" class="form-label">Matrícula</label>
              <input type="text" class="form-control" id="input-matricula" name="matricula" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Curso</label>
              <select name="curso" id="select-curso" class="form-control" required>
                <option value="">Selecione...</option>
                <option value="Engenharia de Software">Engenharia de Software</option>
                <option value="Ciência da Computação">Ciência da Computação</option>
                <option value="Sistemas de Informação">Sistemas de Informação</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-outline" style="padding: 6px 14px; border-radius: var(--r-md);"
              data-bs-dismiss="modal">Cancelar</button>
            <button id="enviar_edicao" type="submit" class="btn-primary"
              style="padding: 6px 14px; border-radius: var(--r-md);">Salvar Alterações</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/service/controle/logout.js"></script>
  <script type="module" src="../../assets/service/aluno/buscar_aluno.js"></script>
  <script type="module" src="../../assets/service/aluno/editar_aluno.js"></script>

</body>

</html>
