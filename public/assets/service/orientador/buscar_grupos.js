import { notificarErro, notificarInfo, notificarSucesso } from "../notificacao/notificacao.js";

document.addEventListener("DOMContentLoaded", (event) => {
  const params = new URLSearchParams(window.location.search);

  const orientadorId = params.get("orientador_id");

  buscarGrupos(orientadorId);
});

async function buscarGrupos(orientadorId) {
  const fd = new FormData();
  fd.append("acao", "buscarGrupos");

  const retorno = await fetch("/gradly/app/controllers/grupo_controller.php", {
    method: "POST",
    body: fd,
  });

  const resposta = await retorno.json();

  if (resposta.success) {
    if (
      !resposta.grupos ||
      resposta.grupos.length === 0 ||
      !resposta.grupos[0].id
    ) {
      document.getElementById("grupos-table-body").innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted py-4">
            Nenhum grupo ou projeto vinculado a este orientador.
          </td>
        </tr>
      `;
      return;
    }

    let linhas = "";

    resposta.grupos.forEach((grupo) => {
      if (!grupo.descricao) {
        grupo.descricao = "Sem descrição";
      }

      linhas += `
        <tr>
          <td>${grupo.nome}</td>

          <td>
            ${
              grupo.integrantes
                ? grupo.integrantes
                    .split(", ")
                    .map((nome) => `<div>${nome}</div>`)
                    .join("")
                : "Sem integrantes"
            }
          </td>

          <td>${grupo.descricao}</td>

          <td>${grupo.dataCriacao}</td>

          <td>
            <button 
              class="btn btn-primary btn-sm btn-ver-grupo"
              data-grupo="${grupo.id}"
            >
              Ver Projeto
            </button>
          </td>
        </tr>
      `;
    });

    document.getElementById("grupos-table-body").innerHTML = linhas;

    document.querySelectorAll(".btn-ver-grupo").forEach((botao) => {
      botao.addEventListener("click", () => {

        const grupoId = botao.dataset.grupo;

        window.location.href =
          `/gradly/public/views/orientador/projeto.php?grupo_id=${grupoId}`;
      });
    });

  } else {
    notificarErro(resposta.message);
  }
}

window.abrirGrupo = function (grupoId) {
  window.location.href =
    `/gradly/public/views/orientador/projeto.php?grupo_id=${grupoId}`;
};