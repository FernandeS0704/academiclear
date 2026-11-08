import { notificarSucesso, notificarErro } from '../notificacao/notificacao.js';
import { validarNome, validarEmail, validarSenha, validarCampo } from '../regex/regex.js';

document.getElementById("cadastrarOrientador").addEventListener("click", (e) =>{
    e.preventDefault();
    cadastrar();
})

async function cadastrar(){
    var nome = document.getElementById('nome').value;
    var email = document.getElementById('email').value;
    var senha = document.getElementById('senha').value;
    var data_cadastro = new Date().toISOString().slice(0, 19).replace('T', ' ');
    var atuacao = document.getElementById('atuacao').value;
    var titulacao = document.getElementById('titulacao').value;

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
    
        if (!validarCampo(atuacao)) {
            notificarErro("Preencha a atuação");
            return;
        }
    
        if(!validarCampo(titulacao)){
            notificarErro("Selecione uma titulação");
            return;
        }


    const fd = new FormData();
    fd.append('nome', nome);
    fd.append('email', email);
    fd.append('senha', senha);
    fd.append('data_cadastro', data_cadastro);
    fd.append('atuacao', atuacao);
    fd.append('titulacao', titulacao);
    fd.append('acao', 'cadastrar');

    const retorno = await fetch("/gradly/app/controllers/orientador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();
        if(resposta.success){
            notificarSucesso(resposta.message, "/gradly/public/views/login.php");
        }else{
            notificarErro(resposta.message);
        }


}