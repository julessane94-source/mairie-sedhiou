CREATE DATABASE IF NOT EXISTS enfance_paix;
USE enfance_paix;

CREATE TABLE administrateurs(
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  mot_de_passe VARCHAR(255)
);

CREATE TABLE agents(
  id_agent INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  mot_de_passe VARCHAR(255),
  fonction VARCHAR(100),
  statut VARCHAR(50)
);

CREATE TABLE documents(
  id_doc INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150),
  description TEXT,
  fichier VARCHAR(255),
  id_agent INT,
  date_upload DATETIME
);

CREATE TABLE formations(
  id_form INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150),
  description TEXT,
  date_debut DATE,
  date_fin DATE
);

CREATE TABLE demandes_stage(
  id_demande INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  email VARCHAR(150),
  message TEXT,
  statut VARCHAR(50)
);

CREATE TABLE infos_entreprise(
  id_info INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150),
  contenu TEXT
);

-- Administrateur par défaut
INSERT INTO administrateurs (nom, prenom, email, mot_de_passe)
VALUES (
  'Sane',
  'Jules',
  'julessane94@gmail.com',
  '$2y$10$hQxZbZz6xZbZz6xZbZz6xO7ZbZz6xZbZz6xZbZz6xZbZz6xZbZz6'
);