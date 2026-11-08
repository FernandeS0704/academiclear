import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', () => {

    const params = new URLSearchParams(window.location.search);

    const orientadorId = params.get("orientador_id");

    buscarProjetos(orientadorId);
});

async function buscarProjetos(orientadorId) {

    const fd = new FormData();

    fd.append('acao', 'buscarProjetoPorOrientador');
    fd.append('orientador_id', orientadorId);

    try {

        const retorno = await fetch(
            "/gradly/app/controllers/projeto_controller.php",
            {
                method: "POST",
                body: fd
            }
        );

        const resposta = await retorno.json();

        if (resposta.success) {

            let linhas = "";

            if (
                !resposta.projetos ||
                resposta.projetos.length === 0
            ) {

                linhas = `
                    <tr>
                        <td colspan="4" class="text-center">
                            Nenhum projeto encontrado
                        </td>
                    </tr>
                `;

            } else {

                resposta.projetos.forEach(projeto => {

                    linhas += `
                        <tr>

                            <td>
                                ${projeto.titulo || "Sem título"}
                            </td>

                            <td>
                                ${projeto.descricao || "Não informado"}
                            </td>

                            <td>
                                ${projeto.estado || "Não definido"}
                            </td>

                            <td>

                                <button 
                                    class="btn btn-primary btn-sm btn-ver-projeto"
                                    data-grupo="${projeto.grupo_id}"
                                >
                                    Ver Projeto
                                </button>

                            </td>

                        </tr>
                    `;
                });
            }

            document.getElementById(
                "projetos-table-body"
            ).innerHTML = linhas;

            document.querySelectorAll(".btn-ver-projeto").forEach((botao) => {
                botao.addEventListener("click", () => {

                    const grupoId = botao.dataset.grupo;

                    window.location.href =
                        `/gradly/public/views/coordenador/projeto.php?grupo_id=${grupoId}`;
                });
            });

        } else {

            notificarErro(resposta.message);
        }

    } catch (error) {

        console.error(error);

        notificarErro("Erro ao buscar projetos.");
    }
}

window.abrirProjeto = function(grupoId) {

    window.location.href =
        `/gradly/public/views/coordenador/projeto.php?grupo_id=${grupoId}`;
}