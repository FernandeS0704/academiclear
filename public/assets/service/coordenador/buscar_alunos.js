import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', (event) => {
    buscarAlunos();
});

async function buscarAlunos(){
    const fd = new FormData();
    fd.append('acao', 'buscarAlunos');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php",{
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();

    if(resposta.success){
        let linhas = "";
        resposta.alunos.forEach(aluno => {
            if(!aluno.titulo_projeto){
                aluno.titulo_projeto = 'Não possui projeto'
            }
            linhas += `
                <tr>
                    <td>${aluno.nome}</td>
                    <td>${aluno.email}</td>
                    <td>${aluno.titulo_projeto}</td>
                    <td>
                        <button 
                            class="btn btn-primary btn-sm btn-ver-projeto"
                            data-grupo="${aluno.grupo_id}"
                        >
                            Ver Projeto
                        </button>
                    </td>
                </tr>
            `;
        });
        document.getElementById("alunos-table-body").innerHTML = linhas;
        document.querySelectorAll(".btn-ver-projeto").forEach((botao) => {
            botao.addEventListener("click", () => {
                const grupoId = botao.dataset.grupo;

                window.location.href =
                    `/gradly/public/views/coordenador/projeto.php?grupo_id=${grupoId}`;
            });
        });
    }else{
        notificarErro(resposta.message);
    }
}

window.abrirProjeto = function(grupoId){
    window.location.href =
        `/gradly/public/views/coordenador/projeto.php?grupo_id=${grupoId}`;
}