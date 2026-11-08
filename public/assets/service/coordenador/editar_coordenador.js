import {notificarErro, notificarInfo, notificarSucesso} from "../notificacao/notificacao.js"
import { validarCampo, validarEmail, validarNome } from "../regex/regex.js";

document.getElementById("enviar_edicao").addEventListener("click", async (e) => {
    e.preventDefault();

    var nome = document.getElementById('input-nome').value;
    var email = document.getElementById('input-email').value;
    var departamento = document.getElementById('select-departamento').value;
    var instituicao = document.getElementById('select-instituicao').value;

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


    if (!validarCampo(departamento)) {
        notificarErro("Preencha o departamento");
        return;
    }


    if(!validarCampo(instituicao)){
        notificarErro("Selecione uma instituição");
        return;
    }
    

    const fd = new FormData();
    fd.append('usuario_id', new URLSearchParams(window.location.search).get('id'));
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('departamento', departamento);
    fd.append('instituicao_id', instituicao);
    fd.append('acao', 'editarCoordenador');

    const retorno = await fetch('/gradly/app/controllers/coordenador_controller.php', {
        method: 'POST',
        body: fd
    });

    const resposta = await retorno.json();
    if (resposta.success) {
        const novoNome = document.getElementById("input-nome").value;
        localStorage.setItem("usuario_nome", novoNome);
        notificarSucesso(resposta.message);
    } else {
        notificarErro(resposta.message);
    }
});