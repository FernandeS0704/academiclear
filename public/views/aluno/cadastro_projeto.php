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
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <title>Gradly — Criar Projeto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --green:        #00a661;
      --green-hover:  #008f52;
      --green-light:  #e8f7f0;
      --green-mid:    #b8e8d2;
      --green-text:   #006b40;
      --bg:           #f9fafb;
      --surface:      #ffffff;
      --surface2:     #f4f5f7;
      --border:       #e8eaed;
      --border-mid:   #d1d5db;
      --text-1:       #1a1a2e;
      --text-2:       #5f6b7a;
      --text-3:       #9aa5b4;
      --sidebar-w:    210px;
      --topbar-h:     52px;
      --r-sm:4px; --r-md:6px; --r-lg:8px; --r-xl:12px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',-apple-system,sans-serif;font-size:13.5px;background:var(--bg);color:var(--text-1);min-height:100vh;}

    /* TOPBAR */
    .topbar{position:fixed;top:0;left:0;right:0;height:var(--topbar-h);background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 1.25rem;z-index:200;gap:0;}
    .topbar-brand{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:600;color:var(--text-1);text-decoration:none;padding-right:1.25rem;border-right:1px solid var(--border);min-width:var(--sidebar-w);}
    .topbar-brand svg{width:22px;height:22px;}
    .topbar-bc{display:flex;align-items:center;gap:6px;padding-left:1.25rem;flex:1;}
    .bc-item{display:flex;flex-direction:column;}
    .bc-label{font-size:9px;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);line-height:1;margin-bottom:2px;}
    .bc-val{display:flex;align-items:center;gap:4px;font-size:13px;font-weight:500;color:var(--text-1);cursor:pointer;}
    .bc-val svg,.bc-arr{width:12px;height:12px;stroke:var(--text-3);fill:none;stroke-width:2;}
    .bc-arr{width:14px;height:14px;margin-top:8px;}
    .topbar-right{display:flex;align-items:center;gap:8px;margin-left:auto;}
    .icon-btn{width:30px;height:30px;border:none;background:transparent;border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);transition:background .12s;}
    .icon-btn:hover{background:var(--surface2);}
    .icon-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;}
    .avatar-btn{width:30px;height:30px;border-radius:50%;background:var(--green);border:none;color:white;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;font-family:inherit;}

    /* SIDEBAR */
    .sidebar{position:fixed;top:var(--topbar-h);left:0;width:var(--sidebar-w);height:calc(100vh - var(--topbar-h));background:var(--surface);border-right:1px solid var(--border);overflow-y:auto;z-index:100;padding:.75rem 0 1.5rem;}
    .sec-label{padding:.9rem 1rem .3rem;font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--text-3);display:flex;align-items:center;justify-content:space-between;}
    .sec-label-inner{display:flex;align-items:center;}
    .si{width:14px;height:14px;stroke:var(--text-2);fill:none;stroke-width:1.75;margin-right:5px;vertical-align:middle;}
    .chev{width:12px;height:12px;stroke:var(--text-3);fill:none;stroke-width:2;}
    .snav{list-style:none;padding-left:0;margin-top:6px;}
    .snav a{display:block;padding:.5rem 1rem .5rem 1.75rem;font-size:13px;color:var(--text-2);text-decoration:none;border-left:2px solid transparent;transition:all .12s;}
    .snav a:hover{background:var(--green-light);color:var(--green-text);}
    .snav a.active{background:var(--green-light);color:var(--green);font-weight:500;border-left-color:var(--green);}

    /* MAIN */
    .main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);min-height:calc(100vh - var(--topbar-h));display:flex;flex-direction:column;}
    .content{display:flex;flex:1;}
    .content-main{flex:1;padding:1.75rem;min-width:0;}
    .page-title{font-size:22px;font-weight:600;letter-spacing:-.02em;color:var(--text-1);margin-bottom:1.25rem;}

    /* PROJECT CARD (form container) */
    .proj-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;max-width:900px;margin:0 auto;}
    .proj-header{padding:.9rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);gap:10px;}
    .proj-card-title{font-size:15px;font-weight:600;color:var(--text-1);margin:0;}
    .card-body{padding:1.25rem;}

    /* FORM */
    .form-label{font-weight:600;color:var(--text-2);font-size:13px;}
    .form-control, textarea.form-control{border:1px solid var(--border);border-radius:6px;background:transparent;}
    .form-control:focus, textarea.form-control:focus{border-color:var(--border-mid);box-shadow:none;}
    .form-control::placeholder{color:var(--text-3);}

    /* BUTTONS */
    .btn-primary{background:var(--green);color:white;border:1px solid var(--green);padding:8px 16px;border-radius:var(--r-md);font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:8px;}
    .btn-primary:hover{background:var(--green-hover);border-color:var(--green-hover);}
    .btn-outline{background:white;color:var(--text-1);border:1px solid var(--border-mid);padding:8px 14px;border-radius:var(--r-md);font-size:13px;font-weight:500;cursor:pointer;}
    .btn-outline:hover{border-color:var(--green);color:var(--green);}

    /* UTIL */
    .muted{color:var(--text-3);font-size:13px;}
    .fi{animation:fadeUp .35s ease both;}
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
  </style>
</head>

<body>
  <!-- TOPBAR -->
<header class="topbar">
  <a class="topbar-brand" href="#">
    Gradly
  </a>

  <div class="topbar-right">
    <button class="icon-btn"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>
    <button class="icon-btn"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></button>
    <button class="icon-btn"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M2 12h2M20 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/></svg></button>
    <a href="perfil.php?id=<?php echo $_SESSION['usuario_id']; ?>" style="text-decoration:none;color:inherit;">
      <span style="text-transform:capitalize">
        <?php echo $_SESSION['usuario_nome']; ?>
      </span>
    </a>
    <button class="icon-btn" id="logout">
      <svg viewBox="0 0 24 24" width="16" height="16" style="stroke:gray;fill:none;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;">
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
        <svg class="si" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Início
      </div>
      <svg class="chev" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>
    </div>

    <ul class="snav">
<li><a href="dashboard_aluno.php" class="active">Dashboard</a></li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="content">
      <div class="content-main">
        <h1 class="page-title fi fi-1">Criar Projeto</h1>

        <div class="proj-card fi fi-2">
          <div class="proj-header">
            <h3 class="proj-card-title">Dados do Projeto</h3>
            <div class="muted">Preencha as informações e clique em "Criar Projeto"</div>
          </div>

          <div class="card-body">
            <form id="form_criar_projeto" action="#" method="POST" novalidate>
              <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input id="titulo" name="titulo" type="text" class="form-control" required placeholder="Ex: Machine Learning para simualação de bactérias" />
              </div>

              <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3" class="form-control" required placeholder="Descreva brevemente o projeto (máx. 500 caracteres)"></textarea>
              </div>

              <div class="mb-3">
                <label for="objetivo" class="form-label">Objetivo</label>
                <textarea id="objetivo" name="objetivo" rows="2" class="form-control" required placeholder="Objetivo principal do projeto"></textarea>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label for="temas" class="form-label">Temas</label>
                  <input id="temas" name="temas" type="text" class="form-control" required placeholder="Ex: Aprendizado de Máquina, Bioinformática" />
                </div>
                <div class="col-md-6">
                  <label for="areas" class="form-label">Áreas</label>
                  <input id="areas" name="areas" type="text" class="form-control" required placeholder="Ex: Computação, Biologia" />
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="button" id="criar_projeto" class="btn-primary">Criar Projeto</button>
                <a href="dashboard_aluno.php" class="btn-outline">Voltar</a>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

    <div style="border-top:1px solid var(--border);padding:.5rem 1.75rem;background:var(--surface);color:var(--text-3);font-size:12px;display:flex;align-items:center;gap:8px;">
      <div style="width:7px;height:7px;border-radius:50%;background:var(--green);"></div>
      Status do sistema: <a href="#" style="color:var(--green);margin-left:6px;text-decoration:none;font-weight:500;">Tudo operacional</a>
    </div>
  </main>

  <script type="module" src="../../assets/service/projeto/criar_projeto.js"></script>
  <script src="../../assets/service/controle/logout.js"></script>
</body>
</html>
