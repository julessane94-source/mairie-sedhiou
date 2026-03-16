-- Base de données complète
CREATE DATABASE IF NOT EXISTS mairie_platform;
USE mairie_platform;

-- Table users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    role ENUM('admin', 'agent', 'public') NOT NULL,
    telephone VARCHAR(20),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

-- Table citoyens
CREATE TABLE IF NOT EXISTS citoyens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    numero_citoyen VARCHAR(20) UNIQUE NOT NULL,
    adresse TEXT,
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table demandes
CREATE TABLE IF NOT EXISTS demandes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_demande VARCHAR(20) UNIQUE NOT NULL,
    citoyen_id INT NOT NULL,
    type_demande ENUM('extrait_naissance', 'declaration_naissance', 'mariage', 'deces', 'residence') NOT NULL,
    statut ENUM('en_attente', 'en_cours', 'traite', 'rejete') DEFAULT 'en_attente',
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_traitement DATETIME,
    agent_id INT,
    commentaire TEXT,
    commentaire_reponse TEXT,
    fichier_joint VARCHAR(255),
    FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table messages
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(200),
    contenu TEXT,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table informations mairie
CREATE TABLE IF NOT EXISTS infos_mairie (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200),
    contenu TEXT,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT,
    categorie VARCHAR(50),
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insertion du compte admin (mot de passe: 123456)
INSERT INTO users (email, password, nom, prenom, role) 
VALUES ('julessane94@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sane', 'Jules', 'admin');

-- Création d'index pour les performances
CREATE INDEX idx_demandes_statut ON demandes(statut);
CREATE INDEX idx_demandes_citoyen ON demandes(citoyen_id);
CREATE INDEX idx_messages_destinataire ON messages(destinataire_id, lu);
CREATE INDEX idx_messages_expediteur ON messages(expediteur_id);