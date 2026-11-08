    DROP DATABASE IF EXISTS gradly;
    CREATE DATABASE gradly;
    USE gradly;

    -- =========== TABELA BASE (HERANÇA) ==============
    CREATE TABLE usuario(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        dataCadastro DATETIME NOT NULL
    );

    -- ============= INSTITUIÇÃO ============
    CREATE TABLE instituicao(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        cnpj VARCHAR(20) NOT NULL,
        endereco VARCHAR(255) NOT NULL,
        telefone VARCHAR(20) NOT NULL
    );

    -- =========== GRUPO ==============
    CREATE TABLE grupo(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT,
        dataCriacao DATETIME NOT NULL
    );

    -- =========== ORIENTADOR ==============
    CREATE TABLE orientador(
        id INT PRIMARY KEY,
        areaAtuacao VARCHAR(255) NOT NULL,
        titulacao VARCHAR(100) NOT NULL,
        FOREIGN KEY (id) REFERENCES usuario(id)
    );

    -- =========== ADMINISTRADOR ==============
    CREATE TABLE administrador(
        id INT PRIMARY KEY,
        nivelAcesso INT NOT NULL,
        FOREIGN KEY (id) REFERENCES usuario(id)
    );

    -- ============ COORDENADOR =============
    CREATE TABLE coordenador(
        id INT PRIMARY KEY,
        departamento VARCHAR(255) NOT NULL,
        instituicao_id INT NOT NULL,
        FOREIGN KEY (id) REFERENCES usuario(id),
        FOREIGN KEY (instituicao_id) REFERENCES instituicao(id)
    );

    -- =========== PROJETO TCC ==============
    CREATE TABLE projeto(
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT,
        objetivo TEXT NOT NULL,
        temas VARCHAR(255) NOT NULL,
        areas VARCHAR(255) NOT NULL,
        estado VARCHAR(50),
        orientador_id INT,
        grupo_id INT NOT NULL,
        FOREIGN KEY (grupo_id) REFERENCES grupo(id),
        FOREIGN KEY (orientador_id) REFERENCES orientador(id)
    );

    -- ========== ALUNO ===============
    CREATE TABLE aluno(
        id INT PRIMARY KEY,
        matricula VARCHAR(50) NOT NULL,
        curso VARCHAR(100) NOT NULL,
        grupo_id INT,
        FOREIGN KEY (id) REFERENCES usuario(id),
        FOREIGN KEY (grupo_id) REFERENCES grupo(id)
    );

    -- ============ DOCUMENTO =============
    CREATE TABLE documento(
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        conteudo TEXT,
        dataCriacao DATETIME NOT NULL,
        path VARCHAR(500),
        versao INT NOT NULL,
        projeto_id INT NOT NULL,
        FOREIGN KEY (projeto_id) REFERENCES projeto(id)
    );

    -- ============ TAREFA =============
    CREATE TABLE tarefa(
        id INT AUTO_INCREMENT PRIMARY KEY,
        descricao TEXT NOT NULL,
        estado VARCHAR(50) NOT NULL,
        dataInicio DATE NOT NULL,
        dataFim DATE NOT NULL,
        responsavel_id INT NOT NULL,
        projeto_id INT NOT NULL,
        FOREIGN KEY (responsavel_id) REFERENCES aluno(id),
        FOREIGN KEY (projeto_id) REFERENCES projeto(id)
    );

    -- ============ COMENTÁRIO =============
    CREATE TABLE comentario(
        id INT AUTO_INCREMENT PRIMARY KEY,
        texto TEXT NOT NULL,
        data_criacao DATETIME NOT NULL,
        autor_id INT NOT NULL,
        documento_id INT NOT NULL,
        FOREIGN KEY (autor_id) REFERENCES usuario(id),
        FOREIGN KEY (documento_id) REFERENCES documento(id)
    );

    -- =========== REFERÊNCIAS ==============
    CREATE TABLE referencias(
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        autor VARCHAR(255) NOT NULL,
        ano INT NOT NULL,
        tipo VARCHAR(50) NOT NULL,
        projeto_id INT NOT NULL,
        FOREIGN KEY (projeto_id) REFERENCES projeto(id)
    );



    -- DADOS DE TESTE


    USE gradly;

    -- CÓDIGO PARA ADCICIONAR CAMPO NA TABELA DO BANCO:
    -- ALTER TABLE aluno ADD COLUMN telefone VARCHAR(20);

    -- ================= INSTITUICOES =================
    INSERT INTO instituicao (id, nome, cnpj, endereco, telefone) VALUES
    (1, 'PUCPR',    '12345678000101', 'Curitiba - PR',   '41999990001'),
    (2, 'UTFPR',    '12345678000102', 'Curitiba - PR',   '41999990002'),
    (3, 'UFPR',     '12345678000103', 'Curitiba - PR',   '41999990003'),
    (4, 'FATEC',    '12345678000104', 'São Paulo - SP',  '11999990004'),
    (5, 'UNINTER',  '12345678000105', 'Curitiba - PR',   '41999990005');


    -- ================= `USER`S =================
    -- IDs 1-5   → Orientadores
    -- IDs 6-10  → Administradores
    -- IDs 11-15 → Coordenadores
    -- IDs 16-20 → Alunos
    INSERT INTO usuario (id, nome, email, senha, dataCadastro) VALUES
    -- Orientadores
    (1,  'Carlos Silva',    'carlos@gradly.com',    '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (2,  'Maria Souza',     'maria@gradly.com',     '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (3,  'Joao Pereira',    'joao@gradly.com',      '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (4,  'Ana Costa',       'ana@gradly.com',       '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (5,  'Pedro Lima',      'pedro@gradly.com',     '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    -- Administradores
    (6,  'Lucas Martins',   'lucas@gradly.com',     '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (7,  'Fernanda Rocha',  'fernanda@gradly.com',  '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (8,  'Rafael Alves',    'rafael@gradly.com',    '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (9,  'Juliana Mendes',  'juliana@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (10, 'Bruno Carvalho',  'bruno@gradly.com',     '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    -- Coordenadores
    (11, 'Patricia Neves',  'patricia@gradly.com',  '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (12, 'Rodrigo Farias',  'rodrigo@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (13, 'Camila Torres',   'camila@gradly.com',    '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (14, 'Eduardo Pinto',   'eduardo@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (15, 'Mariana Lopes',   'mariana@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    -- Alunos
    (16, 'Felipe Gomes',    'felipe@gradly.com',    '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (17, 'Beatriz Santos',  'beatriz@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (18, 'Thiago Ribeiro',  'thiago@gradly.com',    '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (19, 'Larissa Oliveira','larissa@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW()),
    (20, 'Gustavo Ferreira','gustavo@gradly.com',   '$2y$10$N9qo8uLOickgziQueC7B5OPST9QJqq8E4fnxQSDtqKPVXzXvgHAm', NOW());

    -- ================= GRUPOS =================
    INSERT INTO grupo (id, nome, descricao, dataCriacao) VALUES
    (1, 'Grupo IA',        'Pesquisa em Inteligência Artificial', NOW()),
    (2, 'Grupo Web',       'Desenvolvimento Web',                 NOW()),
    (3, 'Grupo Mobile',    'Aplicações Mobile',                   NOW()),
    (4, 'Grupo Dados',     'Ciência de Dados',                    NOW()),
    (5, 'Grupo Segurança', 'Segurança da Informação',             NOW());


    -- ================= ORIENTADORES (IDs 1-5) =================
    INSERT INTO orientador (id, areaAtuacao, titulacao) VALUES
    (1, 'Inteligência Artificial', 'Doutor'),
    (2, 'Desenvolvimento Web',     'Mestre'),
    (3, 'Mobile',                  'Doutor'),
    (4, 'Banco de Dados',          'Mestre'),
    (5, 'Segurança',               'Doutor');


    -- ================= ADMINISTRADORES (IDs 6-10) =================
    INSERT INTO administrador (id, nivelAcesso) VALUES
    (6,  1),
    (7,  2),
    (8,  1),
    (9,  2),
    (10, 1);


    -- ================= COORDENADORES (IDs 11-15) =================
    INSERT INTO coordenador (id, departamento, instituicao_id) VALUES
    (11, 'Engenharia de Software',  1),
    (12, 'Sistemas de Informação',  2),
    (13, 'Ciência da Computação',   3),
    (14, 'Tecnologia',              4),
    (15, 'Computação',              5);


    -- ================= PROJETOS =================
    INSERT INTO projeto (id, titulo, descricao, objetivo, temas, areas, estado, orientador_id, grupo_id) VALUES
    (1, 'Sistema de IA',  'Projeto de IA',        'Criar IA',              'IA',        'Tecnologia', 'Em andamento', 1, 1),
    (2, 'Sistema Web',    'Projeto Web',           'Criar sistema web',     'Web',       'Tecnologia', 'Em andamento', 2, 2),
    (3, 'App Mobile',     'Projeto Mobile',        'Criar app',             'Mobile',    'Tecnologia', 'Planejado',    3, 3),
    (4, 'Banco de Dados', 'Projeto BD',            'Criar BD',              'Dados',     'Tecnologia', 'Em andamento', 4, 4),
    (5, 'Segurança Web',  'Projeto Segurança',     'Criar sistema seguro',  'Segurança', 'Tecnologia', 'Planejado',    5, 5);


    -- ================= ALUNOS (IDs 16-20) =================
    INSERT INTO aluno (id, matricula, curso, grupo_id) VALUES
    (16, '2023001', 'Engenharia de Software', 1),
    (17, '2023002', 'Sistemas de Informação', 2),
    (18, '2023003', 'Ciência da Computação',  3),
    (19, '2023004', 'Engenharia de Software', 4),
    (20, '2023005', 'Sistemas de Informação', 5);


    -- ================= DOCUMENTOS =================
    INSERT INTO documento (id, titulo, conteudo, dataCriacao, versao, projeto_id) VALUES
    (1, 'Documento IA',        'Conteudo IA',        NOW(), 1, 1),
    (2, 'Documento Web',       'Conteudo Web',       NOW(), 1, 2),
    (3, 'Documento Mobile',    'Conteudo Mobile',    NOW(), 1, 3),
    (4, 'Documento BD',        'Conteudo BD',        NOW(), 1, 4),
    (5, 'Documento Segurança', 'Conteudo Segurança', NOW(), 1, 5);


    -- ================= TAREFAS =================
    INSERT INTO tarefa (id, descricao, estado, dataInicio, dataFim, responsavel_id, projeto_id) VALUES
    (1, 'Criar modelo IA', 'Em andamento', '2026-01-01', '2026-06-01', 16, 1),
    (2, 'Criar frontend',  'Em andamento', '2026-01-01', '2026-06-01', 17, 2),
    (3, 'Criar app',       'Planejado',    '2026-01-01', '2026-06-01', 18, 3),
    (4, 'Criar banco',     'Em andamento', '2026-01-01', '2026-06-01', 19, 4),
    (5, 'Criar segurança', 'Planejado',    '2026-01-01', '2026-06-01', 20, 5);


    -- ================= COMENTARIOS =================
    -- Autores distribuídos entre os diferentes tipos de usuário
    INSERT INTO comentario (id, texto, data_criacao, autor_id, documento_id) VALUES
    (1, 'Muito bom',       NOW(), 1,  1),
    (2, 'Precisa melhorar',NOW(), 6,  2),
    (3, 'Ok',              NOW(), 11, 3),
    (4, 'Revisar',         NOW(), 16, 4),
    (5, 'Aprovado',        NOW(), 2,  5);


    -- ================= REFERENCIAS =================
    INSERT INTO referencias (id, titulo, autor, ano, tipo, projeto_id) VALUES
    (1, 'Livro IA',        'Autor A', 2020, 'Livro', 1),
    (2, 'Livro Web',       'Autor B', 2021, 'Livro', 2),
    (3, 'Livro Mobile',    'Autor C', 2022, 'Livro', 3),
    (4, 'Livro BD',        'Autor D', 2023, 'Livro', 4),
    (5, 'Livro Segurança', 'Autor E', 2024, 'Livro', 5);
    
    
    