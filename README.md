#  ALTI — Ecossistema Educacional & Profissional

![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-blue.svg)
![MVC](https://img.shields.io/badge/architecture-MVC-orange.svg)
![MySQL](https://img.shields.io/badge/database-MySQL-blue.svg)
![Security](https://img.shields.io/badge/security-BOLA%20%2F%20IDOR%20Protected-green.svg)

O ALTI é uma plataforma de ecossistema educacional e profissional unificado, projetada para servir como ponte de conexão entre alunos e instituições de ensino. O sistema permite que estudantes divulguem seus progressos acadêmicos, criem portfólios de projetos e interajam com instituições, enquanto estas podem publicar oportunidades, eventos e gerenciar a visibilidade de suas ofertas educacionais.

---

##  Funcionalidades Principais

* **Feed Dinâmico:** Linha do tempo interativa com posts hidratados em tempo real (comentários, likes unificados e curtidas dinâmicas).
* **Portfólio & Perfis:** Visualização especializada para Estudantes (com contadores de progresso e seguidores) e Instituições de Ensino.
* **Segurança Nativa:** Proteção estrita contra manipulação de requisições e filtros automáticos de moderação de comentários.
* **Upload Unificado:** Gerenciamento centralizado de avatares de perfil e mídias para publicações.

---

##  Arquitetura e Tecnologias

A aplicação foi desenvolvida de forma nativa visando alta performance e sem dependências pesadas de frameworks backend:

* **Linguagem:** PHP 8.1+ (com Programação Orientada a Objetos e `strict_types=1`)
* **Banco de Dados:** MySQL (Persistência via Driver `PDO` com padrão **Singleton**)
* **Padrão Arquitetural:** **MVC** (Model-View-Controller) rigoroso
* **Roteamento:** **Front Controller** centralizado no `index.php`

### Documentação Técnica
- [docs/autenticacao-banco-de-dados.md](docs/autenticacao-banco-de-dados.md)

---

##  Estrutura do Projeto

```text
NEXUSSTUDY/
├── config/
│   └── database.php          # Conexão segura e padrão Singleton PDO
├── controllers/
│   ├── AuthController.php    # Autenticação e OAuth Google
│   ├── MainController.php    # Front Controller/roteamento
│   ├── PostController.php    # Gerenciamento de posts e interações
│   ├── ProfileController.php # Perfis e edição de conta
│   └── SearchController.php  # Busca global
├── models/
│   ├── User.php              # Entidade de usuário
│   ├── Post.php              # Entidade de postagem
│   ├── Like.php              # Entidade de like
│   ├── Comment.php           # Entidade de comentário
│   ├── Follow.php            # Entidade de relacionamento
│   ├── Models.php            # Bootstrap das camadas de modelo
│   └── DAO/                  # Camada de persistência
│       ├── UserDAO.php
│       ├── PostDAO.php
│       ├── LikeDAO.php
│       ├── CommentDAO.php
│       └── FollowDAO.php
├── uploads/                  # Armazenamento de avatares e imagens dos posts
├── views/                    # Camada de Apresentação (Interface do Usuário)
│   ├── auth.php              # Login e Cadastro
│   ├── feed_view.php         # Timeline principal do feed
│   ├── profile.php           # Perfil e Portfólio do Aluno
│   ├── institution_profile.php # Perfil público da Instituição
│   └── edit_profile.php      # Formulário de edição de dados
├── docs/
│   └── autenticacao-banco-de-dados.md
├── index.php                 # Ponto de entrada da aplicação (Bootstrap)
└── schema.sql                # Script de criação do Banco de Dados
