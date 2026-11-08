export function validarCampo(texto){
    return texto.trim() !== "";
}

export function validarNome(nome) {
    const regexNome = /^[a-zA-ZÀ-ÿ]+(?:\s[a-zA-ZÀ-ÿ]+)+$/;
    return regexNome.test(nome);
}

export function validarEmail(email) {
    const regexEmail = /^[a-zA-Z0-9._%+-]+@gradly\.com$/;
    return regexEmail.test(email);
}

export function validarSenha(senha) {
    const regexSenha = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return regexSenha.test(senha);
}

export function validarMatricula(matricula) {
    const regexMatricula = /^\d{10,}$/;
    return regexMatricula.test(matricula);
}