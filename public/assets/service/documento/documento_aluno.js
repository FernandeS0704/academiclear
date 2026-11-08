import { notificarSucesso, notificarErro } from '../notificacao/notificacao.js';

const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx'];

document.addEventListener('DOMContentLoaded', () => {
  carregarDocumentos();

  const form = document.getElementById('form-upload-documento');
  const inputArquivo = document.getElementById('arquivo');

  inputArquivo?.addEventListener('change', () => {
    validarArquivoSelecionado(inputArquivo);
  });

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await enviarDocumento();
  });
});

function validarArquivoSelecionado(input) {
  const arquivo = input.files?.[0];
  if (!arquivo) {
    return true;
  }

  const extensao = arquivo.name.split('.').pop()?.toLowerCase();
  if (!extensao || !EXTENSOES_PERMITIDAS.includes(extensao)) {
    notificarErro('Tipo de arquivo não permitido. Envie apenas PDF ou DOC.');
    input.value = '';
    return false;
  }

  const tamanhoMax = 10 * 1024 * 1024;
  if (arquivo.size > tamanhoMax) {
    notificarErro('Arquivo muito grande. Tamanho máximo: 10 MB.');
    input.value = '';
    return false;
  }

  return true;
}

async function carregarDocumentos() {
  const container = document.getElementById('lista-documentos');
  if (!container) return;

  try {
    const fd = new FormData();
    fd.append('acao', 'buscar');

    const retorno = await fetch('/gradly/app/controllers/documento_controller.php', {
      method: 'POST',
      body: fd,
    });

    const resposta = await retorno.json();

    if (!resposta.success) {
      container.innerHTML = `<p class="doc-empty">${resposta.message}</p>`;
      return;
    }

    if (!resposta.documentos || resposta.documentos.length === 0) {
      container.innerHTML = '<p class="doc-empty">Nenhum documento cadastrado.</p>';
      return;
    }

    let linhas = '';
    resposta.documentos.forEach((doc) => {
      const url = resolverUrl(doc.path);
      const extensao = doc.path?.split('.').pop()?.toLowerCase() || '';
      const acao = extensao === 'pdf' ? 'Visualizar' : 'Baixar';

      linhas += `
        <tr>
          <td>${doc.titulo || 'Sem título'}</td>
          <td>v${doc.versao || 1}</td>
          <td>${doc.dataCriacao || '-'}</td>
          <td><a class="doc-link" href="${url}" target="_blank" rel="noopener">${acao}</a></td>
        </tr>
      `;
    });

    container.innerHTML = `
      <table class="doc-table">
        <thead>
          <tr>
            <th>Título</th>
            <th>Versão</th>
            <th>Data</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>${linhas}</tbody>
      </table>
    `;
  } catch (error) {
    container.innerHTML = '<p class="doc-empty">Não foi possível carregar os documentos.</p>';
  }
}

async function enviarDocumento() {
  const inputArquivo = document.getElementById('arquivo');
  if (!validarArquivoSelecionado(inputArquivo)) {
    return;
  }

  const titulo = document.getElementById('titulo').value.trim();
  if (!titulo) {
    notificarErro('Informe o título do documento.');
    return;
  }

  const arquivo = inputArquivo.files[0];
  if (!arquivo) {
    notificarErro('Selecione um arquivo para enviar.');
    return;
  }

  const fd = new FormData();
  fd.append('acao', 'upload');
  fd.append('titulo', titulo);
  fd.append('arquivo', arquivo);

  const btn = document.getElementById('btn-upload');
  btn.disabled = true;

  try {
    const retorno = await fetch('/gradly/app/controllers/documento_controller.php', {
      method: 'POST',
      body: fd,
    });

    const resposta = await retorno.json();

    if (resposta.success) {
      notificarSucesso(resposta.message);
      document.getElementById('form-upload-documento').reset();
      carregarDocumentos();
    } else {
      notificarErro(resposta.message);
    }
  } catch (error) {
    notificarErro('Erro ao enviar o documento.');
  } finally {
    btn.disabled = false;
  }
}

function resolverUrl(path) {
  if (!path) return '#';
  if (path.startsWith('http') || path.startsWith('/')) {
    return path;
  }
  return `/gradly/${path}`;
}
