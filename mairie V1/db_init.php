<?php
/**
 * db_init.php — Initialisation du schéma MySQL (XAMPP)
 * Crée les tables si elles n'existent pas et insère les données de base.
 */

function db_init(PDO $pdo): void {

    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET time_zone = '+01:00'");

    // ── Table users ──────────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            email          VARCHAR(100) NOT NULL UNIQUE,
            password       VARCHAR(255) NOT NULL,
            nom            VARCHAR(50),
            prenom         VARCHAR(50),
            role           ENUM('admin','agent','public') NOT NULL DEFAULT 'public',
            telephone      VARCHAR(20),
            date_creation  DATETIME DEFAULT CURRENT_TIMESTAMP,
            actif          TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── Table citoyens ───────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS citoyens (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            user_id         INT UNIQUE,
            numero_citoyen  VARCHAR(25) UNIQUE,
            adresse         TEXT,
            date_naissance  DATE,
            lieu_naissance  VARCHAR(100),
            sexe            VARCHAR(10) DEFAULT 'M',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── Table demandes ───────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS demandes (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            numero_demande      VARCHAR(30) NOT NULL UNIQUE,
            citoyen_id          INT NOT NULL,
            type_demande        VARCHAR(50) NOT NULL,
            statut              VARCHAR(20) NOT NULL DEFAULT 'en_attente',
            date_demande        DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_traitement     DATETIME,
            agent_id            INT,
            commentaire         TEXT,
            commentaire_reponse TEXT,
            fichier_joint       VARCHAR(255),
            FOREIGN KEY (citoyen_id) REFERENCES citoyens(id) ON DELETE CASCADE,
            FOREIGN KEY (agent_id)   REFERENCES users(id)    ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    try { $pdo->exec("CREATE INDEX idx_demandes_statut  ON demandes(statut)");  } catch(PDOException $e) {}
    try { $pdo->exec("CREATE INDEX idx_demandes_citoyen ON demandes(citoyen_id)"); } catch(PDOException $e) {}

    // ── Table messages ───────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            expediteur_id   INT NOT NULL,
            destinataire_id INT NOT NULL,
            sujet           VARCHAR(200),
            contenu         TEXT,
            date_envoi      DATETIME DEFAULT CURRENT_TIMESTAMP,
            lu              TINYINT(1) DEFAULT 0,
            FOREIGN KEY (expediteur_id)   REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    try { $pdo->exec("CREATE INDEX idx_messages_destinataire ON messages(destinataire_id, lu)"); } catch(PDOException $e) {}
    try { $pdo->exec("CREATE INDEX idx_messages_expediteur   ON messages(expediteur_id)");       } catch(PDOException $e) {}

    // ── Table infos_mairie ───────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS infos_mairie (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            titre            VARCHAR(200),
            contenu          TEXT,
            date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id        INT,
            categorie        VARCHAR(50),
            FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── Table login_attempts (rate limiter) ──────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            ip           VARCHAR(45) NOT NULL,
            email        VARCHAR(255) NOT NULL DEFAULT '',
            success      TINYINT(1) NOT NULL DEFAULT 0,
            attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    try { $pdo->exec("CREATE INDEX idx_ip_email_time ON login_attempts(ip, email, attempted_at)"); } catch(PDOException $e) {}

    // ── Table activity_logs (logger) ─────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_logs (
            id         BIGINT AUTO_INCREMENT PRIMARY KEY,
            level      VARCHAR(20) NOT NULL DEFAULT 'INFO',
            user_id    INT,
            ip         VARCHAR(45) NOT NULL DEFAULT '',
            user_agent VARCHAR(500) NOT NULL DEFAULT '',
            action     VARCHAR(200) NOT NULL,
            details    TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    try { $pdo->exec("CREATE INDEX idx_log_level ON activity_logs(level)");      } catch(PDOException $e) {}
    try { $pdo->exec("CREATE INDEX idx_log_user  ON activity_logs(user_id)");    } catch(PDOException $e) {}
    try { $pdo->exec("CREATE INDEX idx_log_date  ON activity_logs(created_at)"); } catch(PDOException $e) {}

    // ── Compte admin par défaut : julessane94@gmail.com / 123456 ────────────
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'julessane94@gmail.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $admin_hash = '$2y$10$BG.BzQ89QMDw0RI4eaTDZ..tyrJvZvAXuQJfRJm.5ceykRWqyq6aO';
        $pdo->prepare("INSERT INTO users (email, password, nom, prenom, role) VALUES (?, ?, 'Sane', 'Jules', 'admin')")
            ->execute(['julessane94@gmail.com', $admin_hash]);
    }
}

try {
    db_init($pdo);
} catch (PDOException $e) {
    error_log("db_init error: " . $e->getMessage());
}
?>
