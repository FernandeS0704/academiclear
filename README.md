# AcademiClear

Aplicação web responsiva para estudantes organizarem trabalhos e atividades acadêmicas em um só lugar. Projeto acadêmico desenvolvido em grupo.

**Tecnologias:** PHP 8.2+ (orientado a objetos, sem frameworks), HTML5, CSS3, JavaScript, MySQL, PDO, XAMPP, Git e GitHub.

> **Status:** etapas 1 e 2 concluídas (estrutura, banco, conexão PDO, estruturas de dados, Models e Repositories completos e testados). Login, telas, filtros e dashboard virão nas próximas etapas.

---

## Arquitetura

O projeto usa uma arquitetura em camadas, para que cada integrante trabalhe em uma parte sem conflitos:

```
Requisição → public/index.php → Controller → Service → Repository → MySQL (PDO)
                                    ↓
                                  View
```

| Camada | Responsabilidade |
|---|---|
| **View** | HTML apresentado ao usuário. Não acessa o banco. |
| **Controller** | Recebe a requisição, chama os services e escolhe a view. |
| **Service** | Regras de negócio (ex.: validar cadastro, montar dashboard). |
| **Repository** | Único lugar com SQL. Converte linhas do banco em Models. |
| **Model** | Representa uma entidade (Usuario, Disciplina, Tarefa) e valida seus dados. |

## Estrutura de pastas

```
academiclear/
├── app/
│   ├── config/
│   │   ├── config.php            # Constantes, fuso horário e autoload de classes
│   │   ├── Conexao.php           # Conexão PDO centralizada (Singleton)
│   │   ├── config.exemplo.php    # Modelo das credenciais (versionado)
│   │   └── config.local.php      # Credenciais reais (NÃO versionado)
│   ├── models/                   # Usuario, Disciplina, Tarefa
│   ├── controllers/              # (próximas etapas)
│   ├── services/
│   │   └── estruturas/           # ListaEncadeada, NoLista, MapaTarefas, demonstracao.php
│   └── repositories/             # RepositorioBase, exceções, Usuario/Disciplina/TarefaRepository
├── tests/
│   └── testar_repositorios.php   # Testes de Models e Repositories (linha de comando)
├── public/                       # Única pasta acessível pelo navegador
│   ├── css/  js/  images/
│   └── index.php                 # Ponto de entrada (teste de conexão nesta etapa)
├── views/                        # login/, dashboard/, tarefas/, disciplinas/
├── database/
│   └── academiclear.sql          # Script de criação do banco
├── README.md
└── .gitignore
```

## Modelo de dados

- **usuario** (`id_usuario`, `nome`, `email` único, `senha` com hash)
- **disciplina** (`id_disciplina`, `nome`, `id_usuario` → usuario)
- **tarefa** (`id_tarefa`, `titulo`, `descricao`, `prazo`, `prioridade`, `status`, `data_criacao`, `id_disciplina` → disciplina, `id_usuario` → usuario)

Relacionamentos (todos 1:N):

- Um usuário possui várias disciplinas.
- Um usuário possui várias tarefas.
- Uma disciplina possui várias tarefas.
- Cada tarefa pertence a um usuário e a uma disciplina.

As chaves estrangeiras usam `ON DELETE CASCADE`: excluir uma disciplina remove suas tarefas, e excluir um usuário remove disciplinas e tarefas. `prioridade` aceita `alta`, `media`, `baixa`; `status` aceita `pendente`, `em_andamento`, `concluida`.

## Models e Repositories (etapa 2)

### Responsabilidade de cada classe

| Classe | Responsabilidade |
|---|---|
| `Usuario` | Dados do usuário. Valida nome e e-mail. Guarda só o **hash** da senha (`Usuario::novo()`, `alterarSenha()`, `verificarSenha()`). |
| `Disciplina` | Dados da disciplina, sempre vinculada a um `id_usuario`. |
| `Tarefa` | Dados da tarefa. Valida prazo (`AAAA-MM-DD`), prioridade e status. Constantes: `PRIORIDADE_*` e `STATUS_*`. |
| `RepositorioBase` | Guarda o PDO e executa todo SQL por `executar()`, que converte erros do banco em exceções próprias. |
| `RepositorioException` | Erro genérico de banco, sem expor detalhes internos. |
| `RegistroDuplicadoException` | Violação de campo único (e-mail repetido, disciplina com nome repetido). |
| `UsuarioRepository` | `cadastrar`, `buscarPorId`, `buscarPorEmail`, `listarTodos`, `atualizar`, `excluir`. |
| `DisciplinaRepository` | `cadastrar`, `buscarPorId`, `listarPorUsuario`, `atualizar`, `excluir`, `pertenceAoUsuario`. |
| `TarefaRepository` | `cadastrar`, `buscarPorId`, `listarPorUsuario`, `listarPorDisciplina`, `atualizar`, `excluir`, `construirMapa`. |

### Como se relacionam

```
Controller/Service ──usa──> Repository ──devolve──> Model
                               │
                               └─ RepositorioBase ──> Conexao (PDO) ──> MySQL
```

- O Repository recebe e devolve **Models** (nunca arrays soltos).
- `TarefaRepository` usa o `DisciplinaRepository` para conferir se a disciplina pertence ao usuário.
- As listagens de tarefas devolvem uma `ListaEncadeada`; `construirMapa()` devolve um `MapaTarefas`.

### Regras de segurança

- Todo SQL usa prepared statements.
- `buscarPorId`, `atualizar` e `excluir` de disciplinas e tarefas exigem o `id_usuario` no `WHERE`. Um usuário nunca acessa registros de outro.
- Ao cadastrar ou atualizar uma tarefa, a disciplina precisa pertencer ao mesmo usuário (senão lança `InvalidArgumentException`).
- `atualizar()` e `excluir()` devolvem `false` quando o registro não existe ou não é do usuário.

### Exemplos de uso

```php
require_once __DIR__ . '/app/config/config.php';

$usuarios    = new UsuarioRepository();
$disciplinas = new DisciplinaRepository();
$tarefas     = new TarefaRepository();

// Usuário (a senha é transformada em hash automaticamente)
$usuario = $usuarios->cadastrar(Usuario::novo('Ana', 'ana@email.com', 'senha123'));
$achado  = $usuarios->buscarPorEmail('ana@email.com');
$achado->verificarSenha('senha123');   // true (futuro login)

// Disciplina
$disc = $disciplinas->cadastrar(new Disciplina(null, 'Cálculo', $usuario->getIdUsuario()));
$minhas = $disciplinas->listarPorUsuario($usuario->getIdUsuario()); // Disciplina[]

// Tarefa
$tarefa = $tarefas->cadastrar(new Tarefa(
    null, 'Lista 1', 'Exercícios 1 a 10', '2026-12-01',
    Tarefa::PRIORIDADE_ALTA, Tarefa::STATUS_PENDENTE, null,
    $disc->getIdDisciplina(), $usuario->getIdUsuario()
));

foreach ($tarefas->listarPorUsuario($usuario->getIdUsuario()) as $t) {   // ListaEncadeada
    echo $t->getTitulo();
}

$mapa = $tarefas->construirMapa($usuario->getIdUsuario());               // MapaTarefas
$mapa->buscar($tarefa->getIdTarefa());

// Edição e exclusão
$tarefa->setStatus(Tarefa::STATUS_EM_ANDAMENTO);
$tarefas->atualizar($tarefa);
$tarefas->excluir($tarefa->getIdTarefa(), $usuario->getIdUsuario());
```

### Tratamento de erros

```php
try {
    $usuarios->cadastrar(Usuario::novo('Ana', 'ana@email.com', 'senha123'));
} catch (RegistroDuplicadoException $e) {
    echo $e->getMessage();          // "E-mail já cadastrado."
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();          // dado inválido (e-mail, senha curta...)
} catch (RepositorioException $e) {
    echo 'Erro ao acessar o banco.'; // detalhes originais em $e->getPrevious() e no log
}
```

### Valores de status

O banco usa `pendente`, `em_andamento` e `concluida` (com underline, pois é um ENUM). Na tela, o texto exibido pode ser "em andamento"; o valor gravado continua `em_andamento`.

### Executando os testes

Com o MySQL ligado e o banco importado:

```
C:\xampp\php\php.exe tests\testar_repositorios.php
```

O teste cria usuários temporários (`teste.*@academiclear.test`) e **apaga tudo ao final**. Seus dados não são alterados. Ele cobre cadastro, busca, listagem, edição e exclusão das três entidades, além do isolamento entre usuários.

## Estruturas de dados

- **Lista encadeada** (`ListaEncadeada` + `NoLista`): o `TarefaRepository::listarPorUsuario()` guarda nela as tarefas lidas do banco. Os módulos de busca, filtros, ordenação e dashboard vão percorrê-la.
- **HashMap** (`MapaTarefas`): array associativo indexado pelo ID da tarefa, montado a partir da lista (`TarefaRepository::construirMapa()`). Servirá para localizar tarefas por ID em edição, mudança de status e exclusão.

Para ver as estruturas funcionando (sem banco):

```
C:\xampp\php\php.exe app\services\estruturas\demonstracao.php
```

---

## Instalação no XAMPP (Windows)

### 1. Pré-requisitos
- [XAMPP](https://www.apachefriends.org/) com **PHP 8.2 ou superior** (confira em `php -v`).
- [Git](https://git-scm.com/).

### 2. Obter o projeto
Clone o repositório dentro de `C:\xampp\htdocs\`:

```
cd C:\xampp\htdocs
git clone <URL-DO-REPOSITORIO> academiclear
```

### 3. Iniciar os serviços
No **XAMPP Control Panel**, clique em **Start** em **Apache** e **MySQL**.

### 4. Importar o banco pelo phpMyAdmin
1. Acesse `http://localhost/phpmyadmin`.
2. Clique na aba **Importar**.
3. Em **Escolher arquivo**, selecione `database/academiclear.sql`.
4. Clique em **Importar** no fim da página.

O script já cria o banco `academiclear` e as tabelas. Confira na barra lateral.

### 5. Configurar as credenciais
1. Em `app/config/`, copie `config.exemplo.php` para `config.local.php`.
2. Ajuste se necessário. No XAMPP padrão, usuário `root` e senha vazia já funcionam.

O `config.local.php` está no `.gitignore` e nunca é enviado ao GitHub. Cada integrante tem o seu.

### 6. Executar
Acesse: **http://localhost/academiclear/public/**

Deve aparecer "Conexão com o banco de dados estabelecida com sucesso". Se aparecer erro, veja a seção abaixo.

## Problemas comuns

| Sintoma | Causa provável |
|---|---|
| "config.local.php não encontrado" | Falta copiar `config.exemplo.php` para `config.local.php`. |
| "Não foi possível conectar ao banco" | MySQL parado no XAMPP, ou usuário/senha errados no `config.local.php`. |
| "Unknown database 'academiclear'" | O script SQL ainda não foi importado. |
| Porta 3306 ocupada | Altere a porta do MySQL no XAMPP e ajuste `porta` no `config.local.php`. |

## Fluxo de trabalho em grupo (Git)

- `main`: versão estável. Nunca commitar direto nela.
- Cada integrante trabalha em uma branch própria (`feature/login`, `feature/tarefas`, `feature/disciplinas`) e abre Pull Request.
- Antes de começar: `git pull origin main`.
- Mudanças na estrutura do banco: alterar `database/academiclear.sql` e avisar o grupo.
- **Nunca** commitar `config.local.php` ou qualquer senha.

## Convenções

- Classes, métodos e variáveis em português (`TarefaRepository`, `listarPorUsuario`).
- SQL apenas nos repositories, sempre com prepared statements.
- Senhas somente com `password_hash()` e `password_verify()`.
- Autenticação por sessões PHP (próximas etapas).

## Próximas etapas (previstas)

1. Cadastro, login e logout (sessões, controllers e views).
2. Telas de disciplinas.
3. Telas de tarefas, status e prioridade.
4. Busca por título, filtros e ordenação por prazo.
5. Tarefas próximas do vencimento e dashboard.
6. Responsividade e acabamento.
