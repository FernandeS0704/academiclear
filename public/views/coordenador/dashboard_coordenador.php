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
  <meta charset="UTF-8" />
  <title>Gradly — Dashboard do Coordenador</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
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
  
      <a href="perfil.php?id=<?php echo $_SESSION['usuario_id'];?>" style="text-decoration:none;color:inherit;">
        <span style="text-transform:capitalize">
          <?php echo $_SESSION['usuario_nome']; ?>
        </span>
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

  <!-- SIDEBAR -->
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
      <li>
        <a href="dashboard_coordenador.php" class="active">
          Dashboard
        </a>
      </li>
    </ul>

    <div class="sec-label" style="margin-top:.5rem;">

      <div class="sec-label-inner">

        <svg class="si" viewBox="0 0 24 24">
          <ellipse cx="12" cy="5" rx="9" ry="3" />
          <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
          <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
        </svg>

        Sistema

      </div>

    </div>

    <ul class="snav">
      <li><a href="gerenciar_alunos.php">Alunos</a></li>
      <li><a href="gerenciar_orientadores.php">Orientadores</a></li>
      <li><a href="#">Projetos</a></li>
    </ul>

  </aside>

  <!-- MAIN -->
  <main class="main">

    <div class="content">

      <div class="content-main">

        <h1 class="page-title fi fi-2">
          Dashboard do Coordenador
        </h1>

        <!-- METRICS -->
        <div class="metrics-row fi fi-3">

          <!-- ALUNOS -->
          <div class="metric-card">

            <div class="metric-head">

              <span class="metric-lbl">
                Alunos
              </span>

              <div class="metric-ico">
                <svg viewBox="0 0 24 24">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>

            </div>

            <div class="metric-val" id="aluno"></div>

            <p class="metric-sub">
              Total de alunos cadastrados no sistema
            </p>

          </div>

          <!-- ORIENTADORES -->
          <div class="metric-card">

            <div class="metric-head">

              <span class="metric-lbl">
                Orientadores
              </span>

              <div class="metric-ico">
                <svg viewBox="0 0 24 24">
                  <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" />
                </svg>
              </div>

            </div>

            <div class="metric-val" id="orientador"></div>

            <p class="metric-sub">
              Professores ativos para orientação
            </p>


          </div>

          <!-- PROJETOS -->
          <div class="metric-card">

            <div class="metric-head">

              <span class="metric-lbl">
                Projetos
              </span>

              <div class="metric-ico">
                <svg viewBox="0 0 24 24">
                  <path
                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                </svg>
              </div>

            </div>

            <div class="metric-val" id="projeto"></div>

            <p class="metric-sub">
              Projetos acadêmicos registrados
            </p>

          </div>

        </div>

        <!-- PROJECT CARD -->
        <div class="proj-card fi fi-4">
          <!-- META -->
          <div class="proj-meta">

            <div>

              <p class="proj-name">
                Vincular orientador ao projeto
              </p>

              <p class="proj-desc">
                Associe orientadores responsáveis aos projetos acadêmicos cadastrados.
              </p>

            </div>

          </div>

          <!-- FORM -->
          <div style="padding:1.25rem;">

            <div class="row g-3">

              <!-- PROJETO -->
              <div class="col-md-6">

                <label class="form-label" style="font-size:12px;font-weight:600;color:var(--text-2);">
                  Projeto
                </label>

                <select class="form-select" style="height:44px;border-radius:8px;" id='selectProjetos'>
                </select>

              </div>

              <!-- ORIENTADOR -->
              <div class="col-md-6">

                <label class="form-label" style="font-size:12px;font-weight:600;color:var(--text-2);">
                  Orientador
                </label>

                <select class="form-select" style="height:44px;border-radius:8px;" id='selectOrientadores'>
                </select>

              </div>

              <!-- BUTTON -->
              <div class="col-12 d-flex justify-content-end mt-2">

                <button class="btn-primary" id="vincular">

                  <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:white;fill:none;stroke-width:2.5;">
                    <path d="M20 6L9 17l-5-5" />
                  </svg>

                  Vincular orientador

                </button>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </main>

  <script src="../../assets/service/controle/logout.js"></script>
  <script type="module" src="../../assets/service/coordenador/dashboard.js"></script>
  <script type="module" src="../../assets/service/projeto/vincular.js"></script>

</body>

</html>