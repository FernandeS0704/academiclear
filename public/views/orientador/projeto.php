<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['usuario_tipo'] != 'orientador') {
    echo "Acesso negado";
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <title>Gradly — Projeto do Aluno</title>

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
    .sec-label .si{width:14px;height:14px;stroke:var(--text-2);fill:none;stroke-width:1.75;margin-right:5px;vertical-align:middle;}
    .sec-label svg.chev{width:12px;height:12px;stroke:var(--text-3);fill:none;stroke-width:2;}
    .sec-label-inner{display:flex;align-items:center;}
    .snav{list-style:none;}
    .snav a{display:block;padding:.4rem 1rem .4rem 1.75rem;font-size:13px;color:var(--text-2);text-decoration:none;border-left:2px solid transparent;transition:all .12s;line-height:1.4;}
    .snav a:hover{background:var(--green-light);color:var(--green-text);}
    .snav a.active{background:var(--green-light);color:var(--green);font-weight:500;border-left-color:var(--green);}

    /* MAIN */
    .main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);min-height:calc(100vh - var(--topbar-h));display:flex;flex-direction:column;}

    /* ALERT */
    .alert-bar{background:#fffbeb;border-bottom:1px solid #fde68a;padding:.6rem 1.5rem;display:flex;align-items:center;gap:10px;font-size:13px;color:#92400e;position:relative;}
    .alert-bar svg{width:15px;height:15px;flex-shrink:0;}
    .alert-bar a{color:#92400e;font-weight:600;text-decoration:underline;}
    .alert-close{position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-3);font-size:18px;line-height:1;padding:2px 6px;}

    /* CONTENT */
    .content{display:flex;flex:1;gap:0;}
    .content-main{flex:1;padding:1.75rem;min-width:0;}
    .page-title{font-size:22px;font-weight:600;letter-spacing:-.02em;color:var(--text-1);margin-bottom:1.25rem;}

    /* METRICS ROW */
    .metrics-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.25rem;}
    .metric-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);padding:1.1rem 1.25rem;transition:box-shadow .15s,border-color .15s;}
    .metric-card:hover{box-shadow:0 2px 8px rgba(0,0,0,.07);border-color:var(--green-mid);}
    .metric-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem;}
    .metric-lbl{font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-2);}
    .metric-ico{width:28px;height:28px;border-radius:var(--r-md);background:var(--green-light);display:flex;align-items:center;justify-content:center;}
    .metric-ico svg{width:14px;height:14px;stroke:var(--green);fill:none;stroke-width:2;}
    .metric-val{font-size:24px;font-weight:600;letter-spacing:-.03em;color:var(--text-1);line-height:1;margin-bottom:.3rem;}
    .metric-val.empty{font-size:18px;font-weight:400;color:var(--text-3);letter-spacing:.04em;}
    .metric-sub{font-size:11.5px;color:var(--text-3);}
    .prog-track{background:var(--surface2);border-radius:99px;height:4px;overflow:hidden;margin-top:.6rem;}
    .prog-fill{height:100%;background:var(--green);border-radius:99px;width:0;transition:width .8s ease;}

    /* STATUS BADGE */
    .sbadge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:500;}
    .sbadge.pending{background:#fffbeb;color:#92400e;border:1px solid #fde68a;}
    .sbadge.pending::before{content:'';width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;}
    .sbadge.approved{background:var(--green-light);color:var(--green-text);border:1px solid var(--green-mid);}
    .sbadge.approved::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--green);flex-shrink:0;}
    .sbadge.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
    .sbadge.error::before{content:'';width:6px;height:6px;border-radius:50%;background:#ef4444;flex-shrink:0;}

    /* PROJECT CARD */
    .proj-card{background:var(--surface);border:1px solid #c1ccda;border-radius:var(--r-xl);overflow:hidden;}
    .proj-header{padding:.9rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);gap:10px;}
    .proj-card-title{font-size:15px;font-weight:600;color:var(--text-1);margin:0;}
    .proj-meta{padding:.9rem 1.25rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);gap:10px;}
    .proj-name{font-size:14px;font-weight:600;color:var(--text-1);margin-bottom:4px;}
    .proj-desc{font-size:12.5px;color:var(--text-3);}

    .info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;}
    .info-item{padding:.85rem 1.25rem;border-bottom:1px solid var(--border);border-right:1px solid var(--border);}
    .info-item:nth-child(even){border-right:none;}
    .info-item:nth-last-child(-n+2){border-bottom:none;}
    .info-label{font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--text-2);margin-bottom:6px;}
    .info-value{font-size:13.5px;color:var(--text-1);}

    .proj-docs{padding:1rem 1.25rem;border-top:1px solid var(--border);}
    .proj-docs-title{font-size:13px;font-weight:600;color:var(--text-1);margin-bottom:.6rem;}
    .proj-docs-empty{font-size:12.5px;color:var(--text-3);}
    .proj-doc{padding:.6rem .75rem;border:1px solid var(--border);border-radius:var(--r-lg);background:var(--surface2);margin-bottom:.6rem;}
    .proj-doc:last-child{margin-bottom:0;}
    .proj-doc-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:.4rem;}
    .proj-doc-title{font-size:12.8px;font-weight:600;color:var(--text-1);}
    .proj-doc-count{font-size:11.5px;color:var(--text-2);}
    .proj-doc-list{display:flex;flex-direction:column;gap:.25rem;}
    .proj-doc-item{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;font-size:12px;color:var(--text-2);}
    .proj-doc-actions{display:flex;gap:6px;}
    .doc-btn{width:26px;height:26px;border:1px solid var(--border-mid);border-radius:var(--r-md);background:white;display:inline-flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;transition:all .12s;}
    .doc-btn:hover{border-color:var(--green);color:var(--green);}
    .doc-btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.75;}

    .proj-doc-comments{width:100%;padding-top:.4rem;border-top:1px dashed var(--border);margin-top:.35rem;display:flex;flex-direction:column;gap:.35rem;}
    .proj-comment{background:var(--surface);border:1px solid var(--border);border-radius:var(--r-md);padding:.45rem .6rem;}
    .proj-comment-meta{font-size:11px;color:var(--text-3);display:block;margin-bottom:.2rem;}
    .proj-comment-text{font-size:12.5px;color:var(--text-1);margin:0;}
    .proj-comment-empty{font-size:11.5px;color:var(--text-3);}

    .pdf-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:300;}
    .pdf-modal.active{display:flex;}
    .pdf-modal-card{width:min(900px,92vw);height:min(80vh,720px);background:var(--surface);border-radius:var(--r-xl);border:1px solid var(--border);overflow:hidden;display:flex;flex-direction:column;}
    .pdf-modal-head{display:flex;align-items:center;justify-content:space-between;padding:.6rem 1rem;border-bottom:1px solid var(--border);}
    .pdf-modal-title{font-size:13px;font-weight:600;color:var(--text-1);}
    .pdf-modal-body{flex:1;}
    .pdf-modal-body iframe{width:100%;height:100%;border:none;}

    .empty-state{border:1px dashed var(--border-mid);border-radius:var(--r-xl);padding:1.5rem;text-align:center;color:var(--text-2);background:var(--surface);}

    /* BUTTONS */
    .btn-primary{background:var(--green);color:white;border:1px solid var(--green);padding:6px 14px;border-radius:var(--r-md);font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:background .12s;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;}
    .btn-primary:hover{background:var(--green-hover);border-color:var(--green-hover);}
    .btn-outline{background:white;color:var(--text-1);border:1px solid var(--border-mid);padding:6px 14px;border-radius:var(--r-md);font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .12s;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;}
    .btn-outline:hover{border-color:var(--green);color:var(--green);}
    .btn-ghost-sm{background:transparent;color:var(--text-2);border:none;padding:5px 8px;border-radius:var(--r-sm);font-size:12px;font-family:inherit;cursor:pointer;transition:background .12s;}
    .btn-ghost-sm:hover{background:var(--surface2);color:var(--text-1);}
    .btn-dots{background:transparent;border:none;width:28px;height:28px;border-radius:var(--r-md);cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;font-size:18px;line-height:1;transition:background .12s;letter-spacing:1px;}
    .btn-dots:hover{background:var(--surface2);color:var(--text-1);}
    .btn-tag{background:white;border:1px dashed var(--border-mid);color:var(--text-2);padding:3px 10px;border-radius:var(--r-md);font-size:12px;font-family:inherit;cursor:pointer;transition:all .12s;}
    .btn-tag:hover{border-color:var(--green);color:var(--green);border-style:solid;}

    /* RIGHT PANEL */
    .right-panel{width:252px;flex-shrink:0;border-left:1px solid var(--border);background:var(--surface);padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;}
    .panel-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem;}
    .panel-title{font-size:14px;font-weight:600;color:var(--text-1);}
    .panel-tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:.75rem;}
    .ptab{padding:.4rem .7rem;font-size:12.5px;font-weight:500;color:var(--text-2);border-bottom:2px solid transparent;margin-bottom:-1px;cursor:pointer;display:flex;align-items:center;gap:4px;}
    .ptab.active{color:var(--green);border-bottom-color:var(--green);}
    .badge-pill{background:var(--green);color:white;font-size:10px;font-weight:600;padding:1px 5px;border-radius:99px;}
    .tip-item{background:var(--surface2);border:1px solid var(--border);border-radius:var(--r-lg);padding:.65rem .85rem;display:flex;align-items:center;justify-content:space-between;cursor:pointer;}
    .tip-item+.tip-item{margin-top:6px;}
    .tip-lbl{font-size:12.5px;font-weight:500;color:var(--text-1);display:flex;align-items:center;gap:6px;}
    .tip-lbl svg{width:13px;height:13px;stroke:var(--text-2);fill:none;stroke-width:2;}
    .tip-cnt{font-size:11px;color:var(--text-3);margin-left:2px;}
    .tip-chev{width:12px;height:12px;stroke:var(--text-3);fill:none;stroke-width:2;}

    /* STATUS BAR */
    .status-bar{border-top:1px solid var(--border);padding:.5rem 1.75rem;font-size:12px;color:var(--text-3);background:var(--surface);display:flex;align-items:center;gap:6px;}
    .status-dot{width:7px;height:7px;border-radius:50%;background:var(--green);}
    .status-bar a{color:var(--green);text-decoration:none;font-weight:500;}

    /* ANIMATIONS */
    @keyframes fadeUp{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
    .fi{animation:fadeUp .35s ease both;}
    .fi-1{animation-delay:.04s;}.fi-2{animation-delay:.1s;}.fi-3{animation-delay:.17s;}.fi-4{animation-delay:.24s;}.fi-5{animation-delay:.3s;}
  </style>

</head>

<body data-usuario-id="<?= $_SESSION['usuario_id'] ?>">

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

    <a href="perfil.php?id=<?php echo $_SESSION['usuario_id']; ?>" style="text-decoration:none;color:inherit;">
      <span style="text-transform:capitalize"><?php echo $_SESSION['usuario_nome']; ?></span>
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
      <a href="dashboard_orientador.php" class="active">
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
    <li><a href="gerenciar_grupos.php">Grupos</a></li>
  </ul>

</aside>

<!-- MAIN -->
<main class="main">

  <div class="content">
    <div class="content-main">
        <div id="projectContainer" class="fi fi-4"></div>

    </div>

  </div>

</main>

<script src="../../assets/service/controle/logout.js"></script>
<script type="module" src="../../assets/service/projeto/buscar_projeto_orientador.js"></script>

</body>
</html>