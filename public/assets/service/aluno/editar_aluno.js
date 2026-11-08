import { notificarErro, notificarSucesso } from "../notificacao/notificacao.js";
import { validarCampo, validarEmail, validarNome, validarSenha, validarMatricula } from "../regex/regex.js";

document.getElementById("enviar_edicao").addEventListener("click", async (e) => {
    e.preventDefault();

    const nome = document.getElementById("input-nome").value;
    const email = document.getElementById("input-email").value;
    const matricula = document.getElementById("input-matricula").value;
    const curso = document.getElementById("select-curso").value;

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


    if (!validarCampo(matricula)) {
        notificarErro("Preencha a matrícula");
        return;
    }

    if(validarMatricula(matricula) === false){
        notificarErro("Preencha uma matrícula válida (apenas números, com 10 dígitos)");
        return;
    }

    if(!validarCampo(curso)){
        notificarErro("Selecione um curso");
        return;
    }

    const fd = new FormData();
    fd.append('usuario_id', new URLSearchParams(window.location.search).get('id'));
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('matricula', matricula);
    fd.append('curso', curso);
    fd.append('acao', 'editarAluno');

    const retorno = await fetch('/gradly/app/controllers/aluno_controller.php', {
        method: 'POST',
        body: fd
    });

    const resposta = await retorno.json();
    if (resposta.success) {
        localStorage.setItem("usuario_nome", document.getElementById("input-nome").value);
        notificarSucesso(resposta.message);
        setTimeout(() => window.location.reload(), 1500);
    } else {
        notificarErro(resposta.message);
    }
});
