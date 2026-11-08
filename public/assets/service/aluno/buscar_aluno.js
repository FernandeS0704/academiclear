import { notificarErro } from '../notificacao/notificacao.js';

document.addEventListener('DOMContentLoaded', () => {
    buscarAluno();
    configurarModalEditar();
});

let dadosAlunoAtual = null;

function obterIdPerfil() {
    return new URLSearchParams(window.location.search).get('id');
}

async function buscarAluno() {
    const fd = new FormData();
    fd.append('usuario_id', obterIdPerfil());
    fd.append('acao', 'buscarAluno');

    const retorno = await fetch("/gradly/app/controllers/aluno_controller.php", {
        method: "POST",
        body: fd
    });

    const resposta = await retorno.json();
    if (resposta.success) {
        const aluno = resposta.aluno;
        dadosAlunoAtual = aluno;

        const linhas = `
            <tr>
                <td>Nome</td>
                <td>${aluno.nome}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>${aluno.email}</td>
            </tr>
            <tr>
                <td>Matrícula</td>
                <td>${aluno.matricula || 'Não informado'}</td>
            </tr>
            <tr>
                <td>Curso</td>
                <td>${aluno.curso || 'Não informado'}</td>
            </tr>
            <tr>
                <td>Grupo</td>
                <td>${aluno.grupo}</td>
            </tr>
        `;
        document.getElementById("perfil-table").innerHTML = linhas;
    } else {
        notificarErro(resposta.message);
    }
}

function configurarModalEditar() {
    const modalElement = document.getElementById('modalEditarPerfil');

    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', async () => {
            if (dadosAlunoAtual) {
                preencherCamposModal(dadosAlunoAtual);
            } else {
                const aluno = await obterDadosAlunoAPI();
                if (aluno) {
                    preencherCamposModal(aluno);
                }
            }
        });
    }
}

function preencherCamposModal(aluno) {
    document.getElementById("input-nome").value = aluno.nome || '';
    document.getElementById("input-email").value = aluno.email || '';
    document.getElementById("input-matricula").value = aluno.matricula || '';
    document.getElementById("select-curso").value = aluno.curso || '';
}

async function obterDadosAlunoAPI() {
    const fd = new FormData();
    fd.append('usuario_id', obterIdPerfil());
    fd.append('acao', 'buscarAluno');

    try {
        const retorno = await fetch("/gradly/app/controllers/aluno_controller.php", {
            method: "POST",
            body: fd
        });
        const resposta = await retorno.json();
        if (resposta.success) {
            return resposta.aluno;
        }
    } catch (error) {
        console.error("Erro ao buscar dados para o modal:", error);
    }
    return null;
}
