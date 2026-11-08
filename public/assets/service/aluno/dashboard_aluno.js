import { notificarSucesso, notificarErro } from '../notificacao/notificacao.js';

document.addEventListener("DOMContentLoaded", function () {
  const btnProjeto = document.getElementById("criar_projeto");
  const btnGrupo = document.getElementById("criar_grupo");

  if (btnProjeto) {
    btnProjeto.addEventListener("click", function () {
      window.location.href = "cadastro_projeto.php";
    });
  }

  if (btnGrupo) {
    btnGrupo.addEventListener("click", function () {
      window.location.href = "cadastro_grupo.php";
    });
  }

  carregarGrupo();
});

async function carregarGrupo() {
  const container = document.getElementById("grupoContainer");
  if (!container) return;

  try {
    const fd = new FormData();
    fd.append("acao", "buscarMeuGrupo");

    const retorno = await fetch("/gradly/app/controllers/grupo_controller.php", {
      method: "POST",
      body: fd,
    });

    const resposta = await retorno.json();

    if (!resposta.success || !resposta.tem_grupo) {
      container.innerHTML = "";
      return;
    }

    container.innerHTML = `
      <div class="grupo-card fi fi-3">
        <p class="grupo-info">
          Você está no grupo: <strong>${resposta.grupo_nome || "Sem nome"}</strong>
        </p>
        <button type="button" id="btn-sair-grupo" class="btn-danger">Sair do grupo</button>
      </div>
    `;

    document.getElementById("btn-sair-grupo").addEventListener("click", sairGrupo);
  } catch (error) {
    container.innerHTML = "";
  }
}

async function sairGrupo() {
  const confirmar = confirm(
    "Tem certeza que deseja sair do grupo? Você precisará entrar em outro ou criar um novo para continuar o TCC."
  );

  if (!confirmar) return;

  const fd = new FormData();
  fd.append("acao", "sairGrupo");

  const retorno = await fetch("/gradly/app/controllers/grupo_controller.php", {
    method: "POST",
    body: fd,
  });

  const resposta = await retorno.json();

  if (resposta.success) {
    notificarSucesso(resposta.message);
    setTimeout(() => window.location.reload(), 1500);
  } else {
    notificarErro(resposta.message);
  }
}
