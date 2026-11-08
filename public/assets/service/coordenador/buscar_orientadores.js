import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', (event) => {
    buscarOrientadores();
});

async function buscarOrientadores() {
    const fd = new FormData();
    fd.append('acao', 'buscarOrientadores');

    const retorno = await fetch("/gradly/app/controllers/coordenador_controller.php", {
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();

    if (resposta.success) {
        let linhas = "";

        resposta.orientadores.forEach(orientador => {
            linhas += `
                <tr>
                    <td>${orientador.nome}</td>
                    <td>${orientador.email}</td>
                    <td>
                        <button 
                            class="btn btn-primary btn-sm btn-ver-projetos"
                            data-orientador="${orientador.id}"
                        >
                            Ver Projetos
                        </button>
                    </td>
                </tr>
            `;
        });

        document.getElementById("orientadores-table-body").innerHTML = linhas;

        document.querySelectorAll(".btn-ver-projetos").forEach((botao) => {
            botao.addEventListener("click", () => {
                const orientadorId = botao.dataset.orientador;

                window.location.href =
                    `/gradly/public/views/coordenador/projetos_orientador.php?orientador_id=${orientadorId}`;
            });
        });

    } else {
        notificarErro(resposta.message);
    }
}

window.abrirProjetos = function(orientadorId) {
    window.location.href =
        `/gradly/public/views/coordenador/projetos_orientador.php?orientador_id=${orientadorId}`;
}