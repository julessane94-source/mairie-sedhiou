<?php
/**
 * Rate Limiter — Protection anti-force-brute (Version Finale PostgreSQL)
 * Correction complète des types BOOLEAN et des comparaisons de dates.
 */

class RateLimiter {

    private PDO $pdo;
    private int $maxAttempts;
    private int $decaySeconds;
    private int $lockoutSeconds;

    public function __construct(PDO $pdo, int $maxAttempts = 5, int $decaySeconds = 900, int $lockoutSeconds = 900) {
        $this->pdo            = $pdo;
        $this->maxAttempts    = $maxAttempts;
        $this->decaySeconds   = $decaySeconds;
        $this->lockoutSeconds = $lockoutSeconds;
        $this->ensureTable();
    }

    private function ensureTable(): void {
        // On s'assure que la table utilise bien le type BOOLEAN natif de PostgreSQL
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id           SERIAL PRIMARY KEY,
                ip           VARCHAR(45) NOT NULL,
                email        VARCHAR(255) NOT NULL DEFAULT '',
                success      BOOLEAN NOT NULL DEFAULT FALSE,
                attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_ip_email_time ON login_attempts(ip, email, attempted_at)");
    }

    public static function getClientIp(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Enregistre une tentative
     */
    public function record(string $email, bool $success): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (ip, email, success, attempted_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        // On passe explicitement une valeur que PDO peut mapper en BOOLEAN
        $stmt->execute([
            self::getClientIp(), 
            strtolower(trim($email)), 
            $success ? 1 : 0 // PDO transformera le 1/0 en TRUE/FALSE pour Postgres
        ]);

        if (random_int(1, 20) === 1) {
            $this->cleanup();
        }
    }

    /**
     * Vérifie si l'utilisateur est bloqué
     */
    public function check(string $email = ''): array {
        $ip = self::getClientIp();
        
        // Calcul de la fenêtre de temps en PHP pour éviter les erreurs d'opérateurs SQL
        $window = date('Y-m-d H:i:s', time() - (int)$this->decaySeconds);

        // 1. Vérification par IP
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE ip = ? AND success = FALSE AND attempted_at >= ?
        ");
        $stmt->execute([$ip, $window]);
        $attempts_ip = (int)$stmt->fetchColumn();

        // 2. Vérification par Email
        $attempts_email = 0;
        if ($email !== '') {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM login_attempts 
                WHERE email = ? AND success = FALSE AND attempted_at >= ?
            ");
            $stmt->execute([strtolower(trim($email)), $window]);
            $attempts_email = (int)$stmt->fetchColumn();
        }

        $attempts = max($attempts_ip, $attempts_email);

        if ($attempts >= $this->maxAttempts) {
            $stmt = $this->pdo->prepare("
                SELECT MAX(attempted_at) FROM login_attempts
                WHERE ip = ? AND success = FALSE AND attempted_at >= ?
            ");
            $stmt->execute([$ip, $window]);
            $last = $stmt->fetchColumn();
            
            // Calcul du temps restant
            $lastTime = $last ? strtotime($last) : time();
            $remaining = max(0, $this->lockoutSeconds - (time() - $lastTime));

            return [
                'blocked'           => true,
                'remaining_seconds' => (int)$remaining,
                'attempts'          => $attempts,
            ];
        }

        return [
            'blocked'           => false,
            'remaining_seconds' => 0,
            'attempts'          => $attempts,
        ];
    }

    public function clearIp(string $ip): void {
        $this->pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
    }

    private function cleanup(): void {
        $cutoff = date('Y-m-d H:i:s', time() - max($this->decaySeconds, $this->lockoutSeconds) * 2);
        $this->pdo->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$cutoff]);
    }

    public static function formatSeconds(int $seconds): string {
        return $seconds >= 60 ? intdiv($seconds, 60) . ' minute(s)' : $seconds . ' seconde(s)';
    }
}