import { notificarSucesso, notificarErro } from '../notificacao/notificacao.js';
import { validarCampo, validarEmail } from '../regex/regex.js';

const btnCadastrar = document.getElementById("cadastrar");
const btnAdicionar = document.getElementById("adicionar");

if(btnCadastrar){
    btnCadastrar.addEventListener("click", (e) =>{
        e.preventDefault();
        cadastrar();
    });
}

if(btnAdicionar){
    btnAdicionar.addEventListener("click", (e) =>{
        e.preventDefault();
        adicionar();
    });
}

const participantesID = [];
const participantes = [];

async function adicionar(){

    var email = document.getElementById('email').value;
    if(email.trim() === ""){
        notificarErro("Preencha o email");
        return;
    }

    const fd = new FormData();
    fd.append('email', email);
    fd.append('acao', 'adicionar');

    const retorno = await fetch("/gradly/app/controllers/grupo_controller.php",{
        method: "POST",
        body: fd
    }); 

    const resposta = await retorno.json();

    if(resposta.success){        
        participantesID.push(resposta.aluno_id);

        participantes.push({
            email: email
        });

        atualizarParticipantes();

        document.getElementById('email').value = "";

    } else{
        notificarErro(resposta.message);
    }
}

function atualizarParticipantes(){

    const divParticipantes = document.getElementById('participantes');

    divParticipantes.innerHTML = "";

    participantes.forEach((participante) => {

        divParticipantes.innerHTML += `
            <div class="border rounded p-2 mb-2 mt-2">
                <small>${participante.email}</small>
            </div>
        `;
    });
}

async function cadastrar(){

    var nome = document.getElementById('nome').value;
    var descricao = document.getElementById('descricao').value;
    var participantes = JSON.stringify(participantesID);

    if(!validarCampo(nome)){
        notificarErro("Preencha o nome");
        return;
    }

    if(!validarCampo(descricao)){
        notificarErro("Preencha a descrição");
        return;
    }

    const fd = new FormData();
    fd.append('nome', nome);
    fd.append('descricao', descricao);
    fd.append('participantes', participantes);
    fd.append('acao', 'cadastrar');

    const retorno = await fetch("/gradly/app/controllers/grupo_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();

    if(resposta.success){
        notificarSucesso(resposta.message, "cadastro_projeto.php");
    }else{
        notificarErro(resposta.message);
    }
}