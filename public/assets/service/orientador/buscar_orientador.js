import { notificarErro } from "../notificacao/notificacao.js";

document.addEventListener("DOMContentLoaded", () => {
  buscarOrientador();
  configurarModalEditar();
});

let dadosOrientadorAtual = null;

async function buscarOrientador() {
  const fd = new FormData();

  fd.append(
    "usuario_id",
    new URLSearchParams(window.location.search).get("id"),
  );

  fd.append("acao", "buscarOrientador");

  try {
    const retorno = await fetch(
      "/gradly/app/controllers/orientador_controller.php",
      {
        method: "POST",
        body: fd,
      },
    );

    const resposta = await retorno.json();

    if (resposta.success) {
      const orientador = resposta.orientador;

      let linhas = `
        <tr>
          <td>Nome</td>
          <td>${orientador.nome}</td>
        </tr>

        <tr>
          <td>Email</td>
          <td>${orientador.email}</td>
        </tr>

        <tr>
          <td>Área de Atuação</td>
          <td>${orientador.areaAtuacao}</td>
        </tr>

        <tr>
          <td>Titulação</td>
          <td>${orientador.titulacao}</td>
        </tr>
      `;

      document.getElementById("perfil-table").innerHTML = linhas;
    } else {
      notificarErro(resposta.message);
    }
  } catch (error) {
    console.error(error);
    notificarErro("Erro ao buscar orientador");
  }
}

function configurarModalEditar() {
  const modalElement = document.getElementById("modalEditarPerfil");

  if (modalElement) {
    modalElement.addEventListener("show.bs.modal", async () => {
      if (dadosOrientadorAtual) {
        preencherCamposModal(dadosOrientadorAtual);
      } else {
        const orientador = await obterDadosOrientadorAPI();

        if (orientador) {
          preencherCamposModal(orientador);
        }
      }
    });
  }
}

function preencherCamposModal(orientador) {
  document.getElementById("input-nome").value = orientador.nome || "";

  document.getElementById("input-email").value = orientador.email || "";

  document.getElementById("input-area").value = orientador.areaAtuacao || "";

  document.getElementById("input-titulacao").value = orientador.titulacao || "";
}

async function obterDadosOrientadorAPI() {
  const fd = new FormData();

  fd.append(
    "usuario_id",
    new URLSearchParams(window.location.search).get("id"),
  );

  fd.append("acao", "buscarOrientador");

  try {
    const retorno = await fetch(
      "/gradly/app/controllers/orientador_controller.php",
      {
        method: "POST",
        body: fd,
      },
    );

    const resposta = await retorno.json();

    if (resposta.success) {
      return resposta.orientador;
    }
  } catch (error) {
    notificarErro("Erro ao buscar dados:", error);
  }

  return null;
}
