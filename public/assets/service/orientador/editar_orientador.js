import { notificarErro, notificarSucesso } from "../notificacao/notificacao.js";
import { validarCampo, validarEmail, validarNome } from "../regex/regex.js";

document
  .getElementById("enviar_edicao")
  .addEventListener("click", async (e) => {
    e.preventDefault();

    const nome = document.getElementById("input-nome").value;
    const email = document.getElementById("input-email").value;
    const areaAtuacao = document.getElementById("input-area").value;
    const titulacao = document.getElementById("input-titulacao").value;

    if(!validarCampo(nome)){
                notificarErro("Preencha o nome");
                return;
            }
            if(validarNome(nome) === false){
                notificarErro("Preencha com nome e sobrenome (apenas letras)");
                return;
            }
        
            if(!validarCampo(email)){
                notificarErro("Preencha o email");
                return;
            }
        
            if(validarEmail(email) === false){
                notificarErro("Preencha um email válido (exemplo@gradly.com)");
                return;
             }
        
            if (!validarCampo(areaAtuacao)) {
                notificarErro("Preencha a atuação");
                return;
            }
        
            if(!validarCampo(titulacao)){
                notificarErro("Selecione uma titulação");
                return;
            }

    const fd = new FormData();

    fd.append(
      "usuario_id",
      new URLSearchParams(window.location.search).get("id"),
    );

    fd.append("nome", nome);
    fd.append("email", email);
    fd.append("areaAtuacao", areaAtuacao);
    fd.append("titulacao", titulacao);
    fd.append("acao", "editarOrientador");

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
        const novoNome = document.getElementById("input-nome").value;

        localStorage.setItem("usuario_nome", novoNome);

        notificarSucesso(resposta.message);
      } else {
        notificarErro(resposta.message);
      }
    } catch (error) {
      console.error(error);

      notificarErro("Erro ao editar orientador");
    }
  });
