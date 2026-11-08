document.addEventListener("DOMContentLoaded", () => {

  const params = new URLSearchParams(window.location.search);

  const grupoId = params.get("grupo_id");

  buscarProjeto(grupoId);
});

// BUSCA PROJETO NO BACKEND

async function buscarProjeto(grupoId) {

  const container = document.getElementById("projectContainer");

  if (container) {
    container.innerHTML = "";
  }

  try {

    const fd = new FormData();

    fd.append("acao", "buscarProjetoPorGrupo");
    fd.append("grupo_id", grupoId);

    const retorno = await fetch(
      "/gradly/app/controllers/projeto_controller.php",
      {
        method: "POST",
        body: fd
      }
    );

    const resposta = await retorno.json();

    if (!resposta.success || !resposta.data) {

      if (container) {
        renderEmptyState(container, resposta.message);
      }

      return;
    }

    const projetos = Array.isArray(resposta.data)
      ? resposta.data
      : [resposta.data];

    if (container && projetos.length === 0) {

      renderEmptyState(container, resposta.message);

      return;
    }

    if (container) {

      projetos.forEach((projeto) => {

        renderProjeto(container, projeto);
      });
    }

  } catch (error) {

    console.error(error);

    if (container) {

      renderEmptyState(
        container,
        "Não foi possível carregar o projeto."
      );
    }
  }
}


//RENDERIZA COMPONENTE DO PROJETO

function renderProjeto(container, projeto) {

  const card = document.createElement("div");

  card.className = "proj-card";

  const meta = document.createElement("div");

  meta.className = "proj-meta";

  const metaInfo = document.createElement("div");

  const name = document.createElement("p");

  name.className = "proj-name";

  name.textContent =
    projeto.titulo || "Projeto sem título";

  const desc = document.createElement("p");

  desc.className = "proj-desc";

  desc.textContent =
    projeto.descricao || "Sem descrição cadastrada";

  metaInfo.appendChild(name);
  metaInfo.appendChild(desc);

  // COORDENADOR
  const coordenador = document.createElement("div");

  const coordenadorLabel = document.createElement("p");

  coordenadorLabel.className = "proj-desc";

  coordenadorLabel.textContent =
    "Orientador responsável";

  const coordenadorName = document.createElement("p");

  coordenadorName.className = "proj-name";

  coordenadorName.textContent =
    projeto.orientador_nome || "Não definido";

  coordenador.appendChild(coordenadorLabel);
  coordenador.appendChild(coordenadorName);

  meta.appendChild(metaInfo);
  meta.appendChild(coordenador);

  const grid = document.createElement("div");

  grid.className = "info-grid";

  const items = [
    { label: "Objetivo", value: projeto.objetivo },
    { label: "Temas", value: projeto.temas },
    { label: "Áreas", value: projeto.areas },
    { label: "Estado", value: projeto.estado },
  ];

  items.forEach((item) => {

    const cell = document.createElement("div");

    cell.className = "info-item";

    const label = document.createElement("div");

    label.className = "info-label";

    label.textContent = item.label;

    const value = document.createElement("div");

    value.className = "info-value";

    value.textContent =
      item.value || "Não informado";

    cell.appendChild(label);
    cell.appendChild(value);

    grid.appendChild(cell);
  });

  card.appendChild(meta);
  card.appendChild(grid);

  card.appendChild(
    renderDocumentos(projeto.documentos)
  );

  container.appendChild(card);
}

//RENDERIZA DOCUMENTOS

function openPdfViewer(path, title) {
  const modal = ensurePdfModal();
  const iframe = modal.querySelector("iframe");
  const heading = modal.querySelector(".pdf-modal-title");

  iframe.src = resolveDocPath(path);
  heading.textContent = title || "Visualizacao de PDF";
  modal.classList.add("active");
}

function closePdfViewer() {
  const modal = document.getElementById("pdfModal");
  if (!modal) {
    return;
  }

  const iframe = modal.querySelector("iframe");
  iframe.src = "";
  modal.classList.remove("active");
}

function ensurePdfModal() {
  let modal = document.getElementById("pdfModal");
  if (modal) {
    return modal;
  }

  modal = document.createElement("div");
  modal.id = "pdfModal";
  modal.className = "pdf-modal";

  modal.innerHTML = `
    <div class="pdf-modal-card" role="dialog" aria-modal="true">
      <div class="pdf-modal-head">
        <span class="pdf-modal-title">Visualizacao de PDF</span>
        <button class="icon-btn" type="button" aria-label="Fechar">
          <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="pdf-modal-body">
        <iframe title="PDF"></iframe>
      </div>
    </div>
  `;

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      closePdfViewer();
    }
  });

  modal.querySelector("button").addEventListener("click", closePdfViewer);
  document.body.appendChild(modal);
  return modal;
}

function resolveDocPath(path) {
  if (!path) {
    return "";
  }

  if (path.startsWith("http") || path.startsWith("/")) {
    return path;
  }

  return `/gradly/${path}`;
}

function renderDocumentos(documentos) {

  const wrapper = document.createElement("div");

  wrapper.className = "proj-docs";

  const title = document.createElement("h4");

  title.className = "proj-docs-title";

  title.textContent = "Documentos";

  wrapper.appendChild(title);

  if (!documentos || documentos.length === 0) {

    const empty = document.createElement("p");

    empty.className = "proj-docs-empty";

    empty.textContent =
      "Nenhum documento cadastrado.";

    wrapper.appendChild(empty);

    return wrapper;
  }

  documentos.forEach((doc) => {

    const docItem = document.createElement("div");

    docItem.className = "proj-doc";

    const docHead = document.createElement("div");

    docHead.className = "proj-doc-head";

    const docTitle = document.createElement("span");

    docTitle.className = "proj-doc-title";

    docTitle.textContent =
      doc.titulo || "Documento";

    const docCount = document.createElement("span");

    docCount.className = "proj-doc-count";

    docCount.textContent =
      `${doc.versoes?.length || 0} versões`;

    docHead.appendChild(docTitle);
    docHead.appendChild(docCount);

    const list = document.createElement("div");

    list.className = "proj-doc-list";

    (doc.versoes || []).forEach((versao) => {

      const item = document.createElement("div");

      item.className = "proj-doc-item";

      const label = document.createElement("span");

      label.textContent =
        `Versão ${versao.versao || "-"} • ${versao.dataCriacao || ""}`;

      const actions = document.createElement("div");

      actions.className = "proj-doc-actions";

      const viewBtn = document.createElement("button");

      viewBtn.className = "doc-btn";

      viewBtn.type = "button";

      viewBtn.title = "Visualizar PDF";

      viewBtn.innerHTML =
        '<svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>';

      viewBtn.addEventListener("click", () => {

        openPdfViewer(
          versao.path,
          doc.titulo
        );
      });

      actions.appendChild(viewBtn);

      item.appendChild(label);
      item.appendChild(actions);

      const comments =
        versao.comentarios || [];

      const commentsWrap =
        document.createElement("div");

      commentsWrap.className =
        "proj-doc-comments";

      if (comments.length === 0) {

        const emptyComment =
          document.createElement("span");

        emptyComment.className =
          "proj-comment-empty";

        emptyComment.textContent =
          "Sem comentários";

        commentsWrap.appendChild(emptyComment);

      } else {

        comments.forEach((comentario) => {

          const commentRow =
            document.createElement("div");

          commentRow.className =
            "proj-comment";

          const commentMeta =
            document.createElement("span");

          commentMeta.className =
            "proj-comment-meta";

          commentMeta.textContent =
            `${comentario.autor_nome || "Anônimo"} • ${comentario.data_criacao || ""}`;

          const commentText =
            document.createElement("p");

          commentText.className =
            "proj-comment-text";

          commentText.textContent =
            comentario.texto || "";

          commentRow.appendChild(commentMeta);

          commentRow.appendChild(commentText);

          commentsWrap.appendChild(commentRow);
        });
      }

      item.appendChild(commentsWrap);

      list.appendChild(item);
    });

    docItem.appendChild(docHead);
    docItem.appendChild(list);

    wrapper.appendChild(docItem);
  });

  return wrapper;
}


// EMPTY STATE

function renderEmptyState(container, message) {

  const empty = document.createElement("div");

  empty.className = "empty-state";

  const title = document.createElement("p");

  title.style.fontWeight = "600";

  title.style.marginBottom = "6px";

  title.textContent =
    "Nenhum projeto encontrado";

  const subtitle = document.createElement("p");

  subtitle.style.marginBottom = "12px";

  subtitle.textContent =
    message ||
    "Não foi possível carregar o projeto.";

  empty.appendChild(title);

  empty.appendChild(subtitle);

  container.appendChild(empty);
}