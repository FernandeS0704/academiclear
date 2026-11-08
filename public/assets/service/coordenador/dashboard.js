import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', (event) => {
    preencher_cards();
    buscarSemOrientador();
    buscarOrientador();
});

async function preencher_cards(){

    const fd = new FormData();
    fd.append('acao', 'preencher_cards');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();
        if(resposta.success){
            document.getElementById("aluno").innerHTML = `<p>${resposta.alunos}</p>`;
            document.getElementById("orientador").innerHTML = `<p>${resposta.orientadores}</p>`;
            document.getElementById("projeto").innerHTML = `<p>${resposta.projetos}</p>`;
        }else{
            notificarErro(resposta.message);
        }
}

async function buscarSemOrientador(){

    const fd = new FormData();
    fd.append('acao', 'buscarSemOrientador');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();

    if(resposta.success){
        let opcoes = "<option value=''>Selecione um projeto...</option>";
        resposta.projetos.forEach(projeto => {
            opcoes += `
                <option value="${projeto.id}">
                    ${projeto.titulo}
                </option>
            `;
        });
        document.getElementById("selectProjetos").innerHTML = opcoes;
    }else{
        notificarErro(resposta.message);
    }
}

async function buscarOrientador(){

    const fd = new FormData();
    fd.append('acao', 'buscarOrientador');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();

    if(resposta.success){
        let opcoes = "<option value=''>Selecione o orientador...</option>";
        resposta.orientadores.forEach(orientador => {
            opcoes += `
                <option value="${orientador.id}">
                    ${orientador.nome}
                </option>
            `;
        });
        document.getElementById("selectOrientadores").innerHTML = opcoes;
    }else{
        notificarErro(resposta.message);
    }
}