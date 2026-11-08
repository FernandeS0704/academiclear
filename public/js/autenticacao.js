// AcademiClear - botão "Mostrar/Ocultar" dos campos de senha.
// A validação de verdade acontece sempre no servidor; este script só melhora o uso.
document.querySelectorAll('.campo__alternar').forEach(function (botao) {
    botao.addEventListener('click', function () {
        var campo = document.getElementById(botao.dataset.alvo);
        if (!campo) { return; }

        var mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        botao.textContent = mostrar ? 'Ocultar' : 'Mostrar';
    });
});
