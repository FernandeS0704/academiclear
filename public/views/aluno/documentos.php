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
  <title>Gradly — Documentos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --green:#00a661;--green-hover:#008f52;--green-light:#e8f7f0;--green-text:#006b40;
      --bg:#f9fafb;--surface:#fff;--surface2:#f4f5f7;--border:#e8eaed;--border-mid:#d1d5db;
      --text-1:#1a1a2e;--text-2:#5f6b7a;--text-3:#9aa5b4;
      --sidebar-w:210px;--topbar-h:52px;--r-md:6px;--r-lg:8px;--r-xl:12px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',-apple-system,sans-serif;font-size:13.5px;background:var(--bg);color:var(--text-1);min-height:100vh;}
    .topbar{position:fixed;top:0;left:0;right:0;height:var(--topbar-h);background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 1.25rem;z-index:200;}
    .topbar-brand{font-size:15px;font-weight:600;color:var(--text-1);text-decoration:none;padding-right:1.25rem;border-right:1px solid var(--border);min-width:var(--sidebar-w);}
    .topbar-right{display:flex;align-items:center;gap:8px;margin-left:auto;}
    .icon-btn{width:30px;height:30px;border:none;background:transparent;border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-2);}
    .sidebar{position:fixed;top:var(--topbar-h);left:0;width:var(--sidebar-w);height:calc(100vh - var(--topbar-h));background:var(--surface);border-right:1px solid var(--border);padding:.75rem 0 1.5rem;z-index:100;}
    .sec-label{padding:.9rem 1rem .3rem;font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--text-3);}
    .sec-label-inner{display:flex;align-items:center;}
    .si{width:14px;height:14px;stroke:var(--text-2);fill:none;stroke-width:1.75;margin-right:5px;}
    .snav{list-style:none;}
    .snav a{display:block;padding:.4rem 1rem .4rem 1.75rem;font-size:13px;color:var(--text-2);text-decoration:none;border-left:2px solid transparent;}
    .snav a:hover,.snav a.active{background:var(--green-light);color:var(--green);font-weight:500;border-left-color:var(--green);}
    .main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);min-height:calc(100vh - var(--topbar-h));}
    .content-main{padding:1.75rem;}
    .page-title{font-size:22px;font-weight:600;margin-bottom:1.25rem;}
    .proj-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;margin-bottom:1.25rem;max-width:900px;}
    .proj-header{padding:.9rem 1.25rem;border-bottom:1px solid var(--border);}
    .proj-card-title{font-size:15px;font-weight:600;}
    .card-body{padding:1.25rem;}
    .form-label{font-weight:600;color:var(--text-2);font-size:13px;}
    .form-control{border:1px solid var(--border);border-radius:var(--r-md);padding:.55rem .75rem;font-size:13.5px;}
    .form-hint{font-size:12px;color:var(--text-3);margin-top:4px;}
    .btn-primary{background:var(--green);color:#fff;border:1px solid var(--green);padding:8px 16px;border-radius:var(--r-md);font-size:13px;font-weight:500;cursor:pointer;}
    .btn-primary:hover{background:var(--green-hover);}
    .btn-outline{background:#fff;color:var(--text-1);border:1px solid var(--border-mid);padding:8px 14px;border-radius:var(--r-md);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;}
    .btn-outline:hover{border-color:var(--green);color:var(--green);}
    .doc-table{width:100%;border-collapse:collapse;font-size:13px;}
    .doc-table th,.doc-table td{padding:.75rem 1rem;border-bottom:1px solid var(--border);text-align:left;}
    .doc-table th{font-size:11px;text-transform:uppercase;color:var(--text-2);background:var(--surface2);}
    .doc-table tr:hover td{background:var(--green-light);}
    .doc-empty{padding:1.5rem;text-align:center;color:var(--text-3);}
    .doc-link{color:var(--green);font-weight:500;text-decoration:none;}
    .doc-link:hover{text-decoration:underline;}
    .fi{animation:fadeUp .35s ease both;}
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:none;}}
  </style>
</head>
<body>

<header class="topbar">
  <a class="topbar-brand" href="dashboard_aluno.php">Gradly</a>
  <div class="topbar-right">
    <a href="perfil.php?id=<?php echo $_SESSION['usuario_id']; ?>" style="text-decoration:none;color:inherit;">
      <span style="text-transform:capitalize"><?php echo $_SESSION['usuario_nome']; ?></span>
    </a>
    <button class="icon-btn" id="logout" type="button">
      <svg viewBox="0 0 24 24" width="16" height="16" style="stroke:gray;fill:none;stroke-width:1.75;">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </button>
  </div>
</header>

<aside class="sidebar">
  <div class="sec-label"><div class="sec-label-inner"><svg class="si" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>Início</div></div>
  <ul class="snav"><li><a href="dashboard_aluno.php" class="active">Dashboard</a></li></ul>
</aside>

<main class="main">
  <div class="content-main">
    <h1 class="page-title fi">Documentos do Projeto</h1>

    <div class="proj-card fi">
      <div class="proj-header">
        <h3 class="proj-card-title">Enviar documento</h3>
      </div>
      <div class="card-body">
        <form id="form-upload-documento">
          <div class="mb-3">
            <label for="titulo" class="form-label">Título do documento</label>
            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Capítulo 1 - Introdução" required>
            <p class="form-hint">Obrigatório. Usado para identificar o documento e controlar versões.</p>
          </div>
          <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo</label>
            <input type="file" id="arquivo" name="arquivo" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword" required>
            <p class="form-hint">Apenas PDF ou DOC (máx. 10 MB). Nova versão se o título já existir.</p>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" id="btn-upload" class="btn-primary">Enviar documento</button>
            <a href="dashboard_aluno.php" class="btn-outline">Voltar</a>
          </div>
        </form>
      </div>
    </div>

    <div class="proj-card fi">
      <div class="proj-header">
        <h3 class="proj-card-title">Documentos enviados</h3>
      </div>
      <div id="lista-documentos" class="card-body" style="padding:0;">
        <p class="doc-empty">Carregando...</p>
      </div>
    </div>
  </div>
</main>

<script src="../../assets/service/controle/logout.js"></script>
<script type="module" src="../../assets/service/documento/documento_aluno.js"></script>
</body>
</html>
