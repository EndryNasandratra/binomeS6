DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS users;

-- Table des utilisateurs (BackOffice)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Stocker des hash (BCRYPT)
    email VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'un utilisateur admin par défaut (password: admin123)
-- Le mot de passe sera hashé via PHP, mais ici on met un exemple clair
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$O9I02Cr0dFFhO1QPkZceTu5TyUJPdwpJ4PHnnqhZNLTGUJYdteOlC');

-- Table des sections
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Donnees de demonstration des sections
INSERT INTO sections (nom, slug)
VALUES
('Diplomatie', 'diplomatie'),
('Economie', 'economie'),
('International', 'international');

-- Table des articles
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    chapeau TEXT NOT NULL, -- Utilisé aussi pour meta_description
    corps LONGTEXT NOT NULL,
    image_principale VARCHAR(255) DEFAULT NULL,
    image_alt VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    section_id INT NOT NULL DEFAULT 1,
    meta_title VARCHAR(255) DEFAULT NULL, -- SEO Spécifique
    date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_section FOREIGN KEY (section_id) REFERENCES sections(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexation pour recherche et SEO
CREATE INDEX idx_slug ON articles(slug);
CREATE INDEX idx_date_pub ON articles(date_publication);
CREATE INDEX idx_articles_section_id ON articles(section_id);

-- Donnees de demonstration FrontOffice
INSERT INTO articles (titre, chapeau, corps, image_principale, image_alt, slug, section_id, meta_title)
VALUES
('Tensions diplomatiques en hausse', 'Les negociations regionales se durcissent autour du dossier iranien.', 'Le contexte geopolitique evolue rapidement et les acteurs regionaux multiplient les declarations. Ce template sert de base pour integrer des analyses plus detaillees et des sources verifiees.', 'https://images.unsplash.com/photo-1529101091764-c3526daf38fe?auto=format&fit=crop&w=1200&q=80', 'Conflit et diplomatie', 'tensions-diplomatiques-iran', 1, 'Iran: tensions diplomatiques en hausse'),
('Impacts economiques du conflit', 'Les marches de l energie et la logistique regionale sont sous pression.', 'Les fluctuations des prix et les perturbations des chaines d approvisionnement ont des effets directs sur les economies locales. Le FrontOffice met en avant les enjeux economiques avec des pages optimisables SEO.', 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=1200&q=80', 'Graphiques et economie', 'impacts-economiques-conflit', 2, 'Iran: impacts economiques du conflit');
