import { notificarSucesso, notificarErro } from '../notificacao/notificacao.js';
import { validarNome, validarEmail, validarSenha, validarCampo } from '../regex/regex.js';

document.getElementById("cadastrar").addEventListener("click", (e) =>{
    e.preventDefault();
    cadastrar();
})

async function cadastrar(){
    var nome = document.getElementById('nome').value;
    var email = document.getElementById('email').value;
    var senha = document.getElementById('senha').value;
    var data_cadastro = new Date().toISOString().slice(0, 19).replace('T', ' ');
    var departamento = document.getElementById('departamento').value;
    var instituicao = document.getElementById('instituicao').value;

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
    
        if(!validarCampo(senha)){
            notificarErro("Preencha a senha");
            return;
        }
    
    
        if(validarSenha(senha) === false){
            notificarErro("A senha deve conter: <br> • Letra maiúscula, <br> • Letra minúscula, <br> • Número  <br> • 8 caracteres");
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
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('senha', senha);
    fd.append('data_cadastro', data_cadastro);
    fd.append('departamento', departamento);
    fd.append('instituicao', instituicao);
    fd.append('acao', 'cadastrar');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();
        if(resposta.success){
            notificarSucesso(resposta.message, "../login.php");
        }else{
            notificarErro(resposta.message);
        }


}