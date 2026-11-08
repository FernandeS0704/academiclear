import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', (event) => {
    buscarCoordenador();
    configurarModalEditar();
});

let dadosCoordenadorAtual = null;

async function buscarCoordenador(){
    const fd = new FormData();
    fd.append('usuario_id', new URLSearchParams(window.location.search).get('id'));
    fd.append('acao', 'buscarCoordenador');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();
    if(resposta.success){
        const coordenador = resposta.coordenador;
        dadosCoordenadorAtual = coordenador;
        
        let linhas = `
            <tr>
                <td>Nome</td>
                <td>${coordenador.nome}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>${coordenador.email}</td>
            </tr>
            <tr>
                <td>Departamento</td>
                <td>${coordenador.departamento}</td>
            </tr>
            <tr>
                <td>Instituição</td>
                <td>${coordenador.instituicao}</td>
            </tr>
        `;
        document.getElementById("perfil-table").innerHTML = linhas;
    }else{
        notificarErro(resposta.message);
    }
}

function configurarModalEditar() {
    const modalElement = document.getElementById('modalEditarPerfil');
    
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', async () => {
            if (dadosCoordenadorAtual) {
                preencherCamposModal(dadosCoordenadorAtual);
            } else {
                const coordenador = await obterDadosCoordenadorAPI();
                if (coordenador) {
                    preencherCamposModal(coordenador);
                }
            }
        });
    }
}

function preencherCamposModal(coordenador) {
    document.getElementById("input-nome").value = coordenador.nome || '';
    document.getElementById("input-email").value = coordenador.email || '';
    document.getElementById("select-departamento").value = coordenador.departamento || '';
    document.getElementById("select-instituicao").value = coordenador.instituicao_id || '';
}

async function obterDadosCoordenadorAPI() {
    const fd = new FormData();
    fd.append('usuario_id', new URLSearchParams(window.location.search).get('id'));
    fd.append('acao', 'buscarCoordenador');

    try {
        const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php", {
            method: "POST",
            body: fd
        });
        const resposta = await retorno.json();
        if (resposta.success) {
            return resposta.coordenador;
        }
    } catch (error) {
        console.error("Erro ao buscar dados para o modal:", error);
    }
    return null;
}