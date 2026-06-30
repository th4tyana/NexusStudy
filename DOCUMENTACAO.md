#  DOCUMENTAÇÃO TÉCNICA - ALTI

##  1. VISÃO GERAL DO PROJETO

O **ALTI** é uma plataforma de ecossistema educacional e profissional unificado, projetada para servir como ponte de conexão entre alunos e instituições de ensino. O sistema permite que estudantes divulguem seus progressos acadêmicos, criem portfólios de projetos e interajam com instituições, enquanto estas podem publicar oportunidades, eventos e gerenciar a visibilidade de suas ofertas educacionais.

### Objetivo Central
Promover a visibilidade do talento estudantil e facilitar a comunicação direta entre o mundo acadêmico e o profissional, centralizando a interação em um ambiente de rede social especializada em educação.

---

##  2. ARQUITETURA E PADRÕES DE PROJETO

A aplicação foi desenvolvida utilizando **PHP 8.1+** com foco em escalabilidade, segurança e separação de interesses.

### Padrão Arquitetural: MVC (Model-View-Controller)
O projeto adota rigorosamente o padrão MVC para garantir que a lógica de negócio, a persistência de dados e a interface do usuário permaneçam desacopladas:

| Camada | Responsabilidade | Implementação no Projeto |
| :--- | :--- | :--- |
| **Model** | Gestão de dados, consultas SQL e regras de integridade. | `models/Models.php` |
| **View** | Renderização da interface (HTML/CSS/JS) e apresentação de dados. | `views/*.php` |
| **Controller** | Orquestração do fluxo, validação de inputs e controle de rotas. | `controllers/MainController.php` |

### Padrões de Design Utilizados
- **Singleton:** Aplicado na classe `Database` para garantir que apenas uma única conexão PDO seja aberta por requisição, otimizando o consumo de recursos do servidor.
- **Front Controller:** O arquivo `index.php` atua como o único ponto de entrada da aplicação, centralizando a inicialização de sessões e o despacho de requisições para o controlador principal.
- **POO (Programação Orientada a Objetos):** Toda a lógica é encapsulada em classes, utilizando tipagem forte (`declare(strict_types=1)`) para reduzir erros em tempo de execução.

---

##  3. ESTRUTURA DE DIRETÓRIOS E COMPONENTES

Abaixo está o mapeamento funcional de cada componente do sistema:

###  Diretórios
- `config/`: Contém as configurações globais e a conexão com o banco de dados.
- `controllers/`: Camada de controle que processa as requisições e decide qual View exibir.
- `models/`: Camada de abstração de dados que interage com o MySQL.
- `views/`: Templates de interface divididos por contexto (Autenticação, Perfil, Feed).
- `uploads/`: Armazenamento físico de mídias (fotos de perfil e imagens de publicações).

###  Arquivos Principais
| Arquivo | Função |
| :--- | :--- |
| `index.php` | Ponto de entrada. Gerencia o roteamento básico e instancia o `MainController`. |
| `config/database.php` | Implementa a classe `Database` (Singleton) utilizando PDO para conexão segura com MySQL. |
| `controllers/MainController.php` | Cérebro da aplicação. Gerencia login, upload de arquivos, validações de segurança e rotas. |
| `models/Models.php` | Contém as classes `User`, `Post`, `Like`, `Follow` e `Comment` com toda a lógica de CRUD. |
| `schema.sql` | Script de definição do banco de dados, incluindo tabelas, relacionamentos e chaves estrangeiras. |

**Views Específicas:**
- `auth.php`: Interface de Login e Cadastro.
- `profile.php`: Visualização de perfis de usuários e contadores de seguidores/seguindo.
- `edit_profile.php`: Formulário de atualização de dados pessoais e avatar.
- `feed_view.php`: Timeline principal com listagem dinâmica de posts e busca global.
- `institution_profile.php`: Vista especializada para instituições de ensino.

---

##  4. REGRAS DE NEGÓCIO E SEGURANÇA IMPLEMENTADAS

O ALTI implementa camadas de defesa para garantir a integridade dos dados e a privacidade dos usuários.

###  Autenticação e Sessão
- **Hashing de Senhas:** Utilização de `password_hash()` com algoritmo BCRYPT, impedindo a exposição de senhas em texto plano no banco de dados.
- **Gestão de Sessão:** Controle rigoroso via `$_SESSION`, validando a identidade do usuário em todas as rotas protegidas através do método `requireLogin()`.

###  Prevenção contra BOLA/IDOR
Para evitar que usuários mal-intencionados manipulem IDs via URL ou POST para editar perfis ou publicações de terceiros (vulnerabilidades de **Broken Object Level Authorization**), o sistema implementa as seguintes travas no `MainController.php`:

1. **Validação de Propriedade:** No método `canModifyPost()`, o sistema verifica se o `user_id` do autor da publicação é idêntico ao `$_SESSION['user_id']` do usuário logado.
2. **Privilégios Administrativos:** Instituições possuem permissão expandida para moderar conteúdos, mas a edição de perfis de usuários é estritamente limitada ao dono da conta.
3. **Trava de Atualização de Perfil:** O método `profileUpdate()` não aceita um ID via GET/POST para a atualização; ele utiliza obrigatoriamente o ID recuperado da sessão segura do servidor (`$_SESSION['user_id']`), tornando impossível a alteração de outro perfil via manipulação de requisição.

###  Moderação Automática
Implementação de filtro anti-ódio na classe `Comment` (Model), que bloqueia a inserção de comentários contendo termos proibidos (RN03), promovendo um ambiente educacional saudável.

---

##  5. MODELAGEM LOGÍSTICA DE DADOS

A comunicação entre a persistência e a apresentação ocorre de forma fluida através do controlador.

### Fluxo de Consumo de Dados
1. **Feed Dinâmico:** O controlador solicita ao `PostModel` todas as publicações. Antes de enviar para a View, o controlador "hidrata" cada post, injetando a lista de comentários e verificando se o usuário atual já deu "like" naquela publicação.
2. **Renderização de Perfis:** As views de perfil consomem contadores em tempo real do `FollowModel` para exibir a quantidade de seguidores e a lista de pessoas seguidas.
3. **Barra de Progresso e Infos:** Os dados de `extra_info` (como curso ou CNPJ da instituição) são renderizados dinamicamente para contextualizar o perfil do usuário no ecossistema.

### Relacionamentos do Banco de Dados
- **Users $\rightarrow$ Posts:** Um para Muitos (1:N).
- **Users $\rightarrow$ Users (Seguidores):** Muitos para Muitos (N:N) via tabela associativa `seguidores`.
- **Posts $\rightarrow$ Likes:** Muitos para Muitos (N:N) com restrição de unicidade (`UNIQUE KEY`) para evitar likes duplicados do mesmo usuário no mesmo post.
- **Posts $\rightarrow$ Comments:** Um para Muitos (1:N).
