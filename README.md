# AcademiClear

Aplicação web responsiva para estudantes organizarem trabalhos e atividades acadêmicas em um só lugar. Projeto acadêmico desenvolvido em grupo.

**Tecnologias:** PHP 8.2+ (orientado a objetos, sem frameworks), HTML5, CSS3, JavaScript, MySQL, PDO, XAMPP, Git e GitHub.

> **Status:** etapas 1, 2 e 3 concluídas (estrutura, banco, Models, Repositories e autenticação: cadastro, login, sessão e logout). CRUD de disciplinas e tarefas, filtros e dashboard completo virão nas próximas etapas.

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
│   ├── controllers/              # ControllerBase, AutenticacaoController, DashboardController
│   ├── services/
│   │   ├── Sessao.php            # Sessão PHP, flash e CSRF
│   │   ├── AutenticacaoService.php  # Regras de cadastro e login
│   │   ├── ValidacaoException.php, Visao.php
│   │   └── estruturas/           # ListaEncadeada, NoLista, MapaTarefas, demonstracao.php
│   └── repositories/             # RepositorioBase, exceções, Usuario/Disciplina/TarefaRepository
├── tests/
│   ├── testar_repositorios.php   # Testes de Models e Repositories (linha de comando)
│   └── testar_autenticacao.php   # Testes de cadastro e login (linha de comando)
├── public/                       # Única pasta acessível pelo navegador
│   ├── css/estilo.css  js/autenticacao.js  images/
│   ├── index.php                 # Redireciona para login ou dashboard
│   ├── login.php  cadastro.php  dashboard.php  logout.php
│   └── teste_conexao.php         # Página para conferir a conexão com o banco
├── views/                        # login/, dashboard/, compartilhado/, tarefas/, disciplinas/
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

## Autenticação (etapa 3)

### Arquivos e responsabilidades

| Arquivo | Responsabilidade |
|---|---|
| `public/login.php`, `cadastro.php`, `dashboard.php`, `logout.php` | Páginas acessadas pelo navegador. Só carregam a configuração e chamam o controller. |
| `public/index.php` | Envia o usuário ao dashboard (logado) ou ao login. |
| `public/teste_conexao.php` | Antigo `index.php` da etapa 1: confere a conexão com o banco. |
| `AutenticacaoController` | Recebe os formulários, valida o token CSRF, chama o serviço, controla a sessão e redireciona. Tem `exigirAutenticacao()`, que protege as páginas internas. |
| `DashboardController` | Página inicial protegida (saudação e botão Sair). O conteúdo real vem nas próximas etapas. |
| `ControllerBase` | Redirecionamento e leitura de formulário, comuns aos controllers. |
| `AutenticacaoService` | Regras de negócio: valida o cadastro e confere e-mail e senha com `password_verify()`. Usa o `UsuarioRepository` existente. |
| `Sessao` | Único ponto que mexe em `$_SESSION`: login, logout, mensagens e token CSRF. |
| `ValidacaoException` | Carrega as mensagens de erro por campo. |
| `Visao` | Renderiza as views e escapa a saída (`Visao::e()`). |
| `views/login/login.php`, `cadastro.php` | Telas de login e cadastro. |
| `views/dashboard/index.php` | Página inicial protegida. |
| `views/compartilhado/` | Cabeçalho, rodapé, campo de formulário, alertas e tela de erro. |
| `public/css/estilo.css`, `public/js/autenticacao.js` | Visual responsivo e botão "Mostrar/Ocultar senha". |

### Como funciona

- **Cadastro:** nome, e-mail, senha (mínimo 8 caracteres) e confirmação, todos obrigatórios. O e-mail precisa ser válido e único (sem diferenciar maiúsculas). A senha vai para o banco com `password_hash()`.
- **Login:** busca por e-mail e confere com `password_verify()`. Em caso de sucesso, `session_regenerate_id(true)` e redirecionamento ao dashboard. A sessão guarda só o `id` e o `nome` do usuário.
- **Proteção de páginas:** toda página interna deve começar com `AutenticacaoController::exigirAutenticacao()`, que devolve o ID do usuário logado ou redireciona ao login. Esse ID é o que os repositories recebem, então um usuário só enxerga os próprios dados.
- **Logout:** só por `POST` com token CSRF. Limpa a sessão, apaga o cookie e destrói a sessão no servidor.

### Decisões de segurança

- Mensagem única "E-mail ou senha incorretos", sem revelar se o e-mail existe.
- Token CSRF em todos os formulários (cadastro, login e logout).
- Cookie de sessão `HttpOnly` e `SameSite=Lax`, com `session.use_strict_mode`.
- Páginas internas com `Cache-Control: no-store`, para o botão "voltar" não mostrar a tela após o logout.
- Toda saída é escapada com `Visao::e()`.
- A tela de erro de conexão não expõe host, usuário nem SQL.

### Para as próximas páginas internas

```php
require_once __DIR__ . '/../app/config/config.php';

$idUsuario = AutenticacaoController::exigirAutenticacao();
$tarefas = (new TarefaRepository())->listarPorUsuario($idUsuario);
```

### Como testar

**Automático (serviço de cadastro e login):**
```
C:\xampp\php\php.exe tests\testar_autenticacao.php
```

**Manual, no navegador** (`http://localhost/academiclear/public/`):

1. Abra `dashboard.php` sem login: deve ir para o login com o aviso "Faça login...".
2. Em **Cadastre-se**, crie uma conta válida: volta ao login com mensagem de sucesso.
3. Cadastre de novo com o mesmo e-mail: erro "Este e-mail já está cadastrado.".
4. Cadastre com senhas diferentes: erro "As senhas não conferem.".
5. Entre com senha errada: "E-mail ou senha incorretos.".
6. Entre com a senha correta: abre o dashboard com seu nome.
7. Navegue (recarregue, abra `login.php`): continua logado.
8. Clique em **Sair**: volta ao login com "Você saiu da sua conta.".
9. Tente abrir `dashboard.php` ou use o botão "voltar": é redirecionado ao login.

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
Acesse: **http://localhost/academiclear/public/**, que abre a tela de login.

Para conferir só a conexão com o banco, use **http://localhost/academiclear/public/teste_conexao.php**. Se aparecer erro, veja a seção abaixo.

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
- Autenticação por sessões PHP (etapa 3).

## Próximas etapas (previstas)

1. Telas de disciplinas (cadastro, edição e exclusão).
2. Telas de tarefas, status e prioridade.
3. Busca por título, filtros e ordenação por prazo.
4. Tarefas próximas do vencimento e dashboard completo.
5. Responsividade e acabamento.
