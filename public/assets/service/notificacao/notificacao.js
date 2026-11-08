// Estilos do modal de notificação
const notificacaoStyles = `
  :root {
    --green:        #00a661;
    --green-hover:  #008f52;
    --green-light:  #e8f7f0;
    --green-text:   #006b40;
    --bg:           #f9fafb;
    --surface:      #ffffff;
    --surface2:     #f4f5f7;
    --border:       #e8eaed;
    --border-mid:   #d1d5db;
    --text-1:       #1a1a2e;
    --text-2:       #5f6b7a;
    --text-3:       #9aa5b4;
    --r-sm:  4px;
    --r-md:  6px;
    --r-lg:  8px;
    --r-xl:  12px;
  }

  .notificacao-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(26, 26, 46, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn .25s ease;
  }

  .notificacao-modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    padding: 2rem;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    animation: slideUp .35s ease both;
  }

  .notificacao-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
    flex-shrink: 0;
  }

  .notificacao-icon svg {
    width: 36px;
    height: 36px;
    stroke-width: 2.5;
    fill: none;
  }

  .notificacao-icon.sucesso {
    background: var(--green-light);
  }

  .notificacao-icon.sucesso svg {
    stroke: var(--green);
  }

  .notificacao-icon.erro {
    background: #fef2f2;
  }

  .notificacao-icon.erro svg {
    stroke: #ef4444;
  }

  .notificacao-titulo {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-1);
    margin-bottom: 0.5rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }

  .notificacao-mensagem {
    font-size: 13.5px;
    color: var(--text-2);
    line-height: 1.5;
    margin-bottom: 1.75rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }

  .notificacao-botao {
    background: var(--green);
    color: white;
    border: 1px solid var(--green);
    padding: 8px 24px;
    border-radius: var(--r-md);
    font-size: 13px;
    font-weight: 500;
    font-family: inherit;
    cursor: pointer;
    transition: background .12s, border-color .12s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
  }

  .notificacao-botao:hover {
    background: var(--green-hover);
    border-color: var(--green-hover);
  }

  .notificacao-botao.erro {
    background: #ef4444;
    border-color: #ef4444;
  }

  .notificacao-botao.erro:hover {
    background: #dc2626;
    border-color: #dc2626;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
`;

if (!document.getElementById("notificacao-styles")) {
  const style = document.createElement("style");
  style.id = "notificacao-styles";
  style.textContent = notificacaoStyles;
  document.head.appendChild(style);
}

export function notificarSucesso(mensagem, caminho) {
  const overlay = document.createElement("div");
  overlay.className = "notificacao-overlay";

  function fecharModal() {
    overlay.remove();
    if (caminho) {
      window.location.href = caminho;
    } else {
      window.location.reload();
    }
  }

  overlay.innerHTML = `
    <div class="notificacao-modal">
      <div class="notificacao-icon sucesso">
        <svg viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
      <h2 class="notificacao-titulo">Sucesso!</h2>
      <p class="notificacao-mensagem">${mensagem}</p>
      <button class="notificacao-botao">
        Fechar
      </button>
    </div>
  `;

  document.body.appendChild(overlay);

  overlay
    .querySelector(".notificacao-botao")
    .addEventListener("click", fecharModal);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      fecharModal();
    }
  });

  setTimeout(() => {
    if (overlay.parentNode) {
      fecharModal();
    }
  }, 3000);
}

export function notificarErro(mensagem) {
  const overlay = document.createElement("div");
  overlay.className = "notificacao-overlay";
  overlay.innerHTML = `
    <div class="notificacao-modal">
      <div class="notificacao-icon erro">
        <svg viewBox="0 0 24 24">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </div>
      <h2 class="notificacao-titulo">Erro</h2>
      <p class="notificacao-mensagem">${mensagem}</p>
      <button class="notificacao-botao erro" onclick="this.closest('.notificacao-overlay').remove()">
        Fechar
      </button>
    </div>
  `;
  document.body.appendChild(overlay);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      overlay.remove();
    }
  });
}

export function notificarInfo(mensagem) {
  const overlay = document.createElement("div");
  overlay.className = "notificacao-overlay";
  overlay.innerHTML = `
    <div class="notificacao-modal">
      <div class="notificacao-icon" style="background: #e0f2fe;">
        <svg viewBox="0 0 24 24" style="stroke: #0284c7;">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
      </div>
      <h2 class="notificacao-titulo">Informação</h2>
      <p class="notificacao-mensagem">${mensagem}</p>
      <button class="notificacao-botao" onclick="this.closest('.notificacao-overlay').remove()">
        Fechar
      </button>
    </div>
  `;
  document.body.appendChild(overlay);

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      overlay.remove();
    }
  });
}

export function confirmarAcao(mensagem) {
  return new Promise((resolve) => {
    const overlay = document.createElement("div");
    overlay.className = "notificacao-overlay";

    overlay.innerHTML = `
      <div class="notificacao-modal">
        <div class="notificacao-icon" style="background: #fef3c7;">
          <svg viewBox="0 0 24 24" style="stroke: #d97706;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="13"></line>
            <circle cx="12" cy="16" r="1"></circle>
          </svg>
        </div>

        <h2 class="notificacao-titulo">
          Confirmar ação
        </h2>

        <p class="notificacao-mensagem">
          ${mensagem}
        </p>

        <div style="display:flex; gap:10px;">
          <button class="notificacao-botao erro cancelar-btn">
            Cancelar
          </button>

          <button class="notificacao-botao confirmar-btn">
            Confirmar
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);

    function fechar(resultado) {
      overlay.remove();
      resolve(resultado);
    }

    overlay
      .querySelector(".confirmar-btn")
      .addEventListener("click", () => fechar(true));

    overlay
      .querySelector(".cancelar-btn")
      .addEventListener("click", () => fechar(false));

    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        fechar(false);
      }
    });
  });
}
