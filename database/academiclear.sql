-- =====================================================================
-- AcademiClear - Script de criação do banco de dados
-- Compatível com MySQL 5.7+/8.x e MariaDB 10.4+ (XAMPP)
-- Importe pelo phpMyAdmin (aba "Importar") ou pelo terminal.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS academiclear
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE academiclear;

-- ---------------------------------------------------------------------
-- Tabela: usuario
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    senha      VARCHAR(255) NOT NULL COMMENT 'Hash gerado por password_hash()',
    PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuario_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: disciplina
-- Um usuário possui várias disciplinas.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS disciplina (
    id_disciplina INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(100) NOT NULL,
    id_usuario    INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_disciplina),
    -- Impede que o mesmo usuário cadastre duas disciplinas com o mesmo nome
    CONSTRAINT uq_disciplina_usuario_nome UNIQUE (id_usuario, nome),
    CONSTRAINT fk_disciplina_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabela: tarefa
-- Cada tarefa pertence a um usuário e a uma disciplina.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tarefa (
    id_tarefa     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo        VARCHAR(150) NOT NULL,
    descricao     TEXT NULL,
    prazo         DATE NOT NULL,
    prioridade    ENUM('alta', 'media', 'baixa') NOT NULL DEFAULT 'media',
    status        ENUM('pendente', 'em_andamento', 'concluida') NOT NULL DEFAULT 'pendente',
    data_criacao  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_disciplina INT UNSIGNED NOT NULL,
    id_usuario    INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_tarefa),
    CONSTRAINT fk_tarefa_disciplina
        FOREIGN KEY (id_disciplina) REFERENCES disciplina (id_disciplina)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_tarefa_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    -- Índices para as consultas previstas (ordenação por prazo, filtros e busca)
    INDEX idx_tarefa_usuario_prazo      (id_usuario, prazo),
    INDEX idx_tarefa_usuario_status     (id_usuario, status),
    INDEX idx_tarefa_usuario_prioridade (id_usuario, prioridade),
    INDEX idx_tarefa_disciplina         (id_disciplina),
    INDEX idx_tarefa_titulo             (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
