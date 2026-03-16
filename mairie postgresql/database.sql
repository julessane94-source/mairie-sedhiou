-- Suppression des tables si elles existent (pour repartir de zéro)
DROP TABLE IF EXISTS infos_mairie CASCADE;
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS demandes CASCADE;
DROP TABLE IF EXISTS citoyens CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Table users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'agent', 'public')),
    telephone VARCHAR(20),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

-- Table citoyens
CREATE TABLE citoyens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE,
    numero_citoyen VARCHAR(20) UNIQUE NOT NULL,
    adresse TEXT,
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table demandes
CREATE TABLE demandes (
    id SERIAL PRIMARY KEY,
    numero_demande VARCHAR(20) UNIQUE NOT NULL,
    citoyen_id INTEGER NOT NULL,
    type_demande VARCHAR(50) NOT NULL CHECK (type_demande IN ('extrait_naissance', 'declaration_naissance', 'mariage', 'deces', 'residence')),
    statut VARCHAR(20) DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'en_cours', 'traite', 'rejete')),
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_traitement TIMESTAMP,
    agent_id INTEGER,
    commentaire TEXT,
    commentaire_reponse TEXT,
    fichier_joint VARCHAR(255),
    FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table messages
CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    expediteur_id INTEGER NOT NULL,
    destinataire_id INTEGER NOT NULL,
    sujet VARCHAR(200),
    contenu TEXT,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table informations mairie
CREATE TABLE infos_mairie (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200),
    contenu TEXT,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    auteur_id INTEGER,
    categorie VARCHAR(50),
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table rate_limits (Ajoutée pour corriger votre erreur PHP précédente)
CREATE TABLE IF NOT EXISTS rate_limits (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255),
    success BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion du compte admin
INSERT INTO users (email, password, nom, prenom, role) 
VALUES ('julessane94@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sane', 'Jules', 'admin');

-- Création d'index pour les performances
CREATE INDEX idx_demandes_statut ON demandes(statut);
CREATE INDEX idx_demandes_citoyen ON demandes(citoyen_id);
CREATE INDEX idx_messages_destinataire ON messages(destinataire_id, lu);
CREATE INDEX idx_messages_expediteur ON messages(expediteur_id);