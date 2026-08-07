-- ALTI - Schema SQL
-- Execute este arquivo no phpMyAdmin (aba SQL) ou via terminal:
--   mysql -u root -p < schema.sql
-- Compativel com MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS sistema_de_chat_educacional CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_de_chat_educacional;

-- ============================================================
-- TABELA: users
-- ============================================================
DROP TABLE IF EXISTS seguidores;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)  NOT NULL,
    email         VARCHAR(180)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    user_type     ENUM('student','institution') NOT NULL DEFAULT 'student',
    bio           TEXT          DEFAULT NULL,
    avatar_url    VARCHAR(500)  DEFAULT NULL,
    extra_info    VARCHAR(300)  DEFAULT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: posts
-- ============================================================
CREATE TABLE posts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    content    TEXT         NOT NULL,
    media_url  VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: likes
-- ============================================================
CREATE TABLE likes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: seguidores
-- ============================================================
CREATE TABLE seguidores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_seguidor INT UNSIGNED NOT NULL,
    id_seguido INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_seguido) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABELA: comments
-- ============================================================
CREATE TABLE comments (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id    INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    content    TEXT         NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DADOS INICIAIS (seed)
-- Senha de todos os usuarios de demo: Senha@123
-- Hash gerado com password_hash('Senha@123', PASSWORD_DEFAULT) no PHP 8.1
-- ============================================================

INSERT INTO users (name, email, password_hash, user_type, bio, avatar_url, extra_info) VALUES
(
    'SENAI Joinville',
    'senai@educonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'institution',
    'Referencia em educacao profissional e tecnologica. Formando os profissionais do futuro para a industria catarinense.',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSqR5rRDUTBob9eLTsnowntJnun3lt_P7vohuyOCoM4zg&s=10',
    '03.774.819/0001-02'
),
(
    'Gabriel Silva',
    'gabriel@educonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'Estudante de Analise e Desenvolvimento de Sistemas no SENAI. Apaixonado por React, TailwindCSS e arquitetura de software.',
    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=150&q=80',
    'Tecnico em Desenvolvimento de Sistemas'
),
(
    'Ana Costa',
    'ana@educonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'student',
    'Estudante de Design Grafico. Interessada em UX/UI e acessibilidade digital.',
    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80',
    'Tecnico em Design Grafico'
);

INSERT INTO posts (user_id, content, media_url) VALUES
(
    1,
    'Matriculas abertas para os cursos Tecnicos de 2026! Prepare-se para conquistar espaco na industria de tecnologia com as formacoes mais exigidas pelo mercado de Joinville. Bolsas de estudo parciais disponiveis para alunos de escolas publicas. Acesse o nosso perfil e saiba mais!',
    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80'
),
(
    2,
    'Desenvolvi o meu primeiro portfolio completo focado em projetos academicos! Com a ALTI, posso conectar os trabalhos que crio na sala de aula diretamente com o perfil da minha escola. O que acharam do design limpo?',
    'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80'
),
(
    3,
    'Finalizei o projeto de redesign do site institucional da escola! Foi um desafio incrivel trabalhar com acessibilidade e identidade visual ao mesmo tempo. Qualquer feedback e bem-vindo!',
    NULL
);

INSERT INTO likes (post_id, user_id) VALUES (1, 2), (1, 3), (2, 1), (3, 1);

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 2, 'Iniciei o curso este semestre e estou adorando a metodologia por projetos!'),
(1, 3, 'Tenho interesse nas bolsas! Como posso me candidatar?'),
(2, 1, 'Excelente trabalho, Gabriel! O portfolio esta muito bem estruturado.');

ALTER TABLE posts 
ADD COLUMN is_study_guide TINYINT(1) DEFAULT 0,
ADD COLUMN study_topics TEXT NULL;
 
ALTER TABLE posts 
ADD COLUMN course_name VARCHAR(255) NULL,
ADD COLUMN entry_type VARCHAR(100) NULL,
ADD COLUMN weights TEXT NULL,
ADD COLUMN pdf_url VARCHAR(255) NULL,
ADD COLUMN study_topics TEXT NULL;
Caso esteja usando o MySQL 8.0.19 ou superior, pode usar a instrução IF NOT EXISTS para ignorar automaticamente as colunas que já existem:

SQL
ALTER TABLE posts 
ADD COLUMN IF NOT EXISTS is_study_guide TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS course_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS entry_type VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS weights TEXT NULL,
ADD COLUMN IF NOT EXISTS pdf_url VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS study_topics TEXT NULL;
Verificar estrutura da tabela

Para confirmar quais colunas foram adicionadas com sucesso, rode o comando:

SQL
DESCRIBE posts;
