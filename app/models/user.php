<?php

include_once("../config/conexao.php");

    class User{
        public $id;
        public $nome;
        public $email;
        public $senha;
        public $dataCadastro;

        public function inserir() {

            try {

                $parametros = Array(
                    ':nome' => $this->nome,
                    ':email' => $this->email,
                    ':senha' => $this->senha,
                    ':dataCadastro' => $this->dataCadastro,
                );

                $query = "INSERT INTO usuario 
                        (nome, email, senha, dataCadastro)
                        VALUES
                        (:nome, :email, :senha, :dataCadastro)";

                Conexao::executarComParametros($query, $parametros);

                return Conexao::conectar()->lastInsertId();

            } catch (Exception $e) {
                throw new Exception("Erro ao inserir usuário: " . $e->getMessage());
            }
        }

        public function editarUser(){
            try {
                $parametros = Array(
                    ':id' => $this->id,
                    ':nome' => $this->nome,
                    ':email' => $this->email,
                );

                $query = "UPDATE usuario SET 
                        nome = :nome, 
                        email = :email 
                        WHERE id = :id";

                Conexao::executarComParametros($query, $parametros);

                return true;

            } catch (Exception $e) {
                throw new Exception("Erro ao editar usuário: " . $e->getMessage());
            }
        }
    }
?>