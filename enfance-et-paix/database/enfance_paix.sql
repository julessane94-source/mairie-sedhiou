-- Création de la base de données
CREATE DATABASE IF NOT EXISTS enfance_paix;
USE enfance_paix;

-- ==========================================================
-- 1. TABLES DES UTILISATEURS (ADMINS & AGENTS)
-- ==========================================================

CREATE TABLE IF NOT EXISTS administrateurs(
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  mot_de_passe VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agents(
  id_agent INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  mot_de_passe VARCHAR(255),
  fonction VARCHAR(100),
  statut VARCHAR(50)
) ENGINE=InnoDB;

-- ==========================================================
-- 2. GESTION DES FORMATIONS ET DEMANDES (ADMIN)
-- ==========================================================

-- Résout l'erreur : Table 'enfance_paix.formation' doesn't exist
CREATE TABLE IF NOT EXISTS formation(
  id_form INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150),
  `nom complet` VARCHAR(255),
  `telephone` VARCHAR(50),
  `formation souhaitée` VARCHAR(255),
  description TEXT,
  date_debut DATE,
  date_fin DATE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS demandes_stage(
  id_demande INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150),
  message TEXT,
  statut VARCHAR(50)
) ENGINE=InnoDB;

-- ==========================================================
-- 3. ESPACE COLLABORATIF (CHAT, PROJETS, CLOUD)
-- ==========================================================

-- Résout l'erreur : Table 'enfance_paix.messages' doesn't exist
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur VARCHAR(100) NOT NULL DEFAULT 'Agent',
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_projet VARCHAR(255) NOT NULL,
    progression INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'En cours'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents(
  id_doc INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150),
  description TEXT,
  fichier VARCHAR(255),
  id_agent INT,
  date_upload DATETIME,
  FOREIGN KEY (id_agent) REFERENCES agents(id_agent) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================================================
-- 4. DONNÉES DE DÉPART (ADMIN & TESTS)
-- ==========================================================

-- Administrateur par défaut
INSERT IGNORE INTO administrateurs (nom, prenom, email, mot_de_passe)
VALUES ('Sane', 'Jules', 'julessane94@gmail.com', '123456');

-- Agent de test pour le module Profil
INSERT IGNORE INTO agents (id_agent, nom, prenom, email, mot_de_passe, fonction, statut)
VALUES (1, 'Sane', 'Jules', 'agent@enfancepaix.sn', '123456', 'Agent de terrain', 'Actif');

-- Exemples de projets pour l'espace collaboratif
INSERT INTO projets (nom_projet, progression, statut) VALUES 
('Refonte du site web', 75, 'En cours'),
('Campagne de formation', 10, 'En attente');

-- Message de bienvenue pour le Chat Interne
INSERT INTO messages (expediteur, message) VALUES 
('Système', 'Bienvenue dans votre nouvel espace collaboratif !');