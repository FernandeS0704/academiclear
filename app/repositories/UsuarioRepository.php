<?php
declare(strict_types=1);

/**
 * Acesso ao banco para a tabela "usuario".
 *
 * A senha sempre trafega como hash (gerado no model Usuario).
 * Não há operação que devolva a senha em texto puro, pois ela nunca é guardada.
 */
class UsuarioRepository extends RepositorioBase
{
    /**
     * Cadastra o usuário e devolve o mesmo objeto, já com o ID gerado.
     *
     * @throws RegistroDuplicadoException se o e-mail já estiver cadastrado
     */
    public function cadastrar(Usuario $usuario): Usuario
    {
        if ($this->buscarPorEmail($usuario->getEmail()) !== null) {
            throw new RegistroDuplicadoException('E-mail já cadastrado.');
        }

        $this->executar(
            'INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)',
            [
                ':nome'  => $usuario->getNome(),
                ':email' => $usuario->getEmail(),
                ':senha' => $usuario->getSenha(),
            ],
            'E-mail já cadastrado.'
        );

        $usuario->setIdUsuario((int) $this->pdo->lastInsertId());
        return $usuario;
    }

    public function buscarPorId(int $idUsuario): ?Usuario
    {
        $linha = $this->buscarLinha('SELECT * FROM usuario WHERE id_usuario = :id', [':id' => $idUsuario]);
        return $linha ? Usuario::deArray($linha) : null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $linha = $this->buscarLinha(
            'SELECT * FROM usuario WHERE email = :email',
            [':email' => strtolower(trim($email))]
        );
        return $linha ? Usuario::deArray($linha) : null;
    }

    /** @return Usuario[] */
    public function listarTodos(): array
    {
        return array_map([Usuario::class, 'deArray'], $this->buscarLinhas('SELECT * FROM usuario ORDER BY nome'));
    }

    /**
     * Atualiza nome, e-mail e senha (hash) do usuário.
     *
     * @return bool false se o usuário não existir
     * @throws RegistroDuplicadoException se o novo e-mail já pertencer a outro usuário
     */
    public function atualizar(Usuario $usuario): bool
    {
        $id = $usuario->getIdUsuario();
        if ($id === null || $this->buscarPorId($id) === null) {
            return false;
        }

        $this->executar(
            'UPDATE usuario SET nome = :nome, email = :email, senha = :senha WHERE id_usuario = :id',
            [
                ':nome'  => $usuario->getNome(),
                ':email' => $usuario->getEmail(),
                ':senha' => $usuario->getSenha(),
                ':id'    => $id,
            ],
            'E-mail já cadastrado.'
        );

        return true;
    }

    /** Exclui o usuário (disciplinas e tarefas dele saem junto, por ON DELETE CASCADE). */
    public function excluir(int $idUsuario): bool
    {
        return $this->executar('DELETE FROM usuario WHERE id_usuario = :id', [':id' => $idUsuario])->rowCount() > 0;
    }
}
