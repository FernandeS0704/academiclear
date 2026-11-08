document.getElementById("logout").addEventListener("click", (e) => {
    e.preventDefault();
    confirmarLogout();
});

function confirmarLogout() {

    const overlay = document.createElement('div');
    overlay.className = 'notificacao-overlay';

    overlay.innerHTML = `
        <div class="notificacao-modal">

            <div 
                class="notificacao-icon"
                style="background: #fef3c7;"
            >
                <svg 
                    viewBox="0 0 24 24"
                    style="stroke: #d97706;"
                >
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                </svg>
            </div>

            <h2 class="notificacao-titulo">
                Confirmar saída
            </h2>

            <p class="notificacao-mensagem">
                Tem certeza que deseja sair do sistema?
            </p>

            <div 
                style="
                    display:flex;
                    gap:10px;
                    width:100%;
                    justify-content:center;
                "
            >

                <button 
                    class="notificacao-botao"
                    id="cancelar-logout"
                    style="
                        background:#f4f5f7;
                        color:#1a1a2e;
                        border:1px solid #d1d5db;
                    "
                >
                    Cancelar
                </button>

                <button 
                    class="notificacao-botao erro"
                    id="confirmar-logout"
                >
                    Sair
                </button>

            </div>

        </div>
    `;

    document.body.appendChild(overlay);

    document
        .getElementById("cancelar-logout")
        .addEventListener("click", () => {
            overlay.remove();
        });

    document
        .getElementById("confirmar-logout")
        .addEventListener("click", async () => {

            await logout();

            overlay.remove();
        });

    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

async function logout() {

    await fetch("/gradly/app/controllers/login_controller.php", {
        method: "POST",
        body: new URLSearchParams({ acao: "logout" })
    });

    window.location.href = "/gradly/public/views/login.php";
}