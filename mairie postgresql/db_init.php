<?php
/**
 * db_init.php - Initialisation du schéma PostgreSQL
 * Crée les tables si elles n'existent pas et insère les données de base
 */

function db_init(PDO $pdo): void {
    // Table users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id             SERIAL PRIMARY KEY,
            email          VARCHAR(100) UNIQUE NOT NULL,
            password       VARCHAR(255) NOT NULL,
            nom            VARCHAR(50),
            prenom         VARCHAR(50),
            role           VARCHAR(20) NOT NULL DEFAULT 'public' CHECK (role IN ('admin','agent','public')),
            telephone      VARCHAR(20),
            date_creation  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actif          BOOLEAN DEFAULT TRUE
        )
    ");

    // Table citoyens - N°CIT = CIT-AAAAMMJJ-NNNNN
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS citoyens (
            id              SERIAL PRIMARY KEY,
            user_id         INTEGER UNIQUE REFERENCES users(id) ON DELETE CASCADE,
            numero_citoyen  VARCHAR(25) UNIQUE,
            adresse         TEXT,
            date_naissance  DATE,
            lieu_naissance  VARCHAR(100),
            sexe            VARCHAR(10) DEFAULT 'M'
        )
    ");

    // Table demandes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS demandes (
            id                  SERIAL PRIMARY KEY,
            numero_demande      VARCHAR(30) UNIQUE NOT NULL,
            citoyen_id          INTEGER NOT NULL REFERENCES citoyens(id) ON DELETE CASCADE,
            type_demande        VARCHAR(50) NOT NULL,
            statut              VARCHAR(20) NOT NULL DEFAULT 'en_attente',
            date_demande        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_traitement     TIMESTAMP,
            agent_id            INTEGER REFERENCES users(id) ON DELETE SET NULL,
            commentaire         TEXT,
            commentaire_reponse TEXT,
            fichier_joint       VARCHAR(255)
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_demandes_statut  ON demandes(statut)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_demandes_citoyen ON demandes(citoyen_id)");

    // Table messages
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id              SERIAL PRIMARY KEY,
            expediteur_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            destinataire_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            sujet           VARCHAR(200),
            contenu         TEXT,
            date_envoi      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            lu              BOOLEAN DEFAULT FALSE
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_destinataire ON messages(destinataire_id, lu)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_expediteur   ON messages(expediteur_id)");

    // Table infos_mairie
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS infos_mairie (
            id               SERIAL PRIMARY KEY,
            titre            VARCHAR(200),
            contenu          TEXT,
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            auteur_id        INTEGER REFERENCES users(id) ON DELETE SET NULL,
            categorie        VARCHAR(50)
        )
    ");

    // Table login_attempts (rate limiter)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id           SERIAL PRIMARY KEY,
            ip           VARCHAR(45) NOT NULL,
            email        VARCHAR(255) NOT NULL DEFAULT '',
            success      BOOLEAN NOT NULL DEFAULT FALSE,
            attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ip_email_time ON login_attempts(ip, email, attempted_at)");

    // Table activity_logs (logger)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_logs (
            id         BIGSERIAL PRIMARY KEY,
            level      VARCHAR(20) NOT NULL DEFAULT 'INFO',
            user_id    INTEGER,
            ip         VARCHAR(45) NOT NULL DEFAULT '',
            user_agent VARCHAR(500) NOT NULL DEFAULT '',
            action     VARCHAR(200) NOT NULL,
            details    TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_level ON activity_logs(level)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_user  ON activity_logs(user_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_date  ON activity_logs(created_at)");

    // Compte admin par défaut: julessane94@gmail.com / 123456
    $admin_hash = '$2y$10$BG.BzQ89QMDw0RI4eaTDZ..tyrJvZvAXuQJfRJm.5ceykRWqyq6aO';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'julessane94@gmail.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (email, password, nom, prenom, role) VALUES (?, ?, 'Sane', 'Jules', 'admin')")
            ->execute(['julessane94@gmail.com', $admin_hash]);
    }
}

try {
    db_init($pdo);
} catch (PDOException $e) {
    // Ne pas bloquer si l'init échoue (ex : déjà initialisé)
    error_log("db_init error: " . $e->getMessage());
}
?>
