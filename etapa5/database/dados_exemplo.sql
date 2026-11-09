-- =====================================================================
-- AcademiClear - Dados de exemplo (opcional, apenas para desenvolvimento)
--
-- Cria disciplinas e tarefas para um usuário JÁ CADASTRADO, para você
-- ver o dashboard com conteúdo (indicadores, filtros, atrasadas etc.).
--
-- COMO USAR:
--   1. Cadastre-se normalmente pela tela de cadastro do sistema.
--   2. Troque o e-mail na linha "SET @email" pelo e-mail que você cadastrou.
--   3. Importe este arquivo pelo phpMyAdmin (aba "Importar") ou pelo terminal.
--
-- Os prazos são relativos à data de hoje (CURDATE), então sempre haverá
-- tarefas atrasadas, de hoje e futuras.
--
-- Rode UMA vez por usuário: se rodar de novo, as tarefas serão duplicadas
-- (as disciplinas não, pois o nome é único por usuário).
-- Para limpar, exclua as disciplinas pelo phpMyAdmin: as tarefas saem junto.
-- =====================================================================

USE academiclear;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @email := 'troque-aqui@seu-email.com';
SET @uid := (SELECT id_usuario FROM usuario WHERE email = @email);

-- Se o e-mail não existir, @uid fica NULL e NADA é criado (confira o resultado
-- no final do script: "tarefas_do_usuario" deve mostrar 12).

INSERT IGNORE INTO disciplina (nome, id_usuario)
SELECT n.nome, @uid
FROM (
    SELECT 'Banco de Dados' AS nome
    UNION ALL SELECT 'Estruturas de Dados'
    UNION ALL SELECT 'Engenharia de Software'
    UNION ALL SELECT 'Programação Web'
    UNION ALL SELECT 'Cálculo I'   -- fica sem tarefas, para testar a disciplina vazia
) AS n
WHERE @uid IS NOT NULL;

INSERT INTO tarefa (titulo, descricao, prazo, prioridade, status, id_disciplina, id_usuario)
SELECT v.titulo, v.descricao, CURDATE() + INTERVAL v.dias DAY, v.prioridade, v.status, d.id_disciplina, @uid
FROM (
    SELECT 'Lista de exercícios 3' AS titulo, 'Consultas com JOIN e subconsultas (questões 1 a 12).' AS descricao, -4 AS dias, 'alta' AS prioridade, 'pendente' AS status, 'Banco de Dados' AS disciplina
    UNION ALL SELECT 'Modelo entidade-relacionamento', 'Entregar o diagrama e o dicionário de dados.', -1, 'alta', 'em_andamento', 'Banco de Dados'
    UNION ALL SELECT 'Normalização até a 3FN', NULL, 6, 'media', 'pendente', 'Banco de Dados'
    UNION ALL SELECT 'Implementar lista encadeada', 'Inserção no início e no fim, remoção por critério e iterador.', 0, 'alta', 'em_andamento', 'Estruturas de Dados'
    UNION ALL SELECT 'Seminário: tabelas hash', 'Preparar 10 slides sobre colisões e tratamento por encadeamento.', 9, 'media', 'pendente', 'Estruturas de Dados'
    UNION ALL SELECT 'Revisão para a prova', NULL, 3, 'alta', 'pendente', 'Estruturas de Dados'
    UNION ALL SELECT 'Relatório de requisitos', 'Levantamento de requisitos funcionais e não funcionais.', -10, 'media', 'concluida', 'Engenharia de Software'
    UNION ALL SELECT 'Diagrama de casos de uso', NULL, 1, 'baixa', 'pendente', 'Engenharia de Software'
    UNION ALL SELECT 'Protótipo das telas', 'Protótipo navegável das telas de login e dashboard.', 14, 'baixa', 'pendente', 'Engenharia de Software'
    UNION ALL SELECT 'Tela de login responsiva', 'HTML e CSS, sem frameworks.', -6, 'media', 'concluida', 'Programação Web'
    UNION ALL SELECT 'Validação de formulário', 'Validar no servidor e mostrar as mensagens ao usuário.', 2, 'alta', 'em_andamento', 'Programação Web'
    UNION ALL SELECT 'Revisão de acessibilidade', NULL, 20, 'baixa', 'pendente', 'Programação Web'
) AS v
JOIN disciplina d ON d.nome = v.disciplina AND d.id_usuario = @uid;

-- Conferência: deve mostrar o ID do usuário e 12 tarefas.
-- Se aparecer NULL e 0, o e-mail informado em "SET @email" não está cadastrado.
SELECT @uid AS id_usuario_encontrado,
       (SELECT COUNT(*) FROM tarefa WHERE id_usuario = @uid) AS tarefas_do_usuario;
