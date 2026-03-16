<?php
/**
 * Rate Limiter — Protection anti-force-brute (MySQL)
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
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                ip           VARCHAR(45) NOT NULL,
                email        VARCHAR(255) NOT NULL DEFAULT '',
                success      TINYINT(1) NOT NULL DEFAULT 0,
                attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        try { $this->pdo->exec("CREATE INDEX idx_ip_email_time ON login_attempts(ip, email, attempted_at)"); } catch(PDOException $e) {}
    }

    public static function getClientIp(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    public function record(string $email, bool $success): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (ip, email, success, attempted_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([self::getClientIp(), strtolower(trim($email)), $success ? 1 : 0]);

        if (random_int(1, 20) === 1) $this->cleanup();
    }

    public function check(string $email = ''): array {
        $ip     = self::getClientIp();
        $window = date('Y-m-d H:i:s', time() - $this->decaySeconds);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE ip = ? AND success = 0 AND attempted_at >= ?
        ");
        $stmt->execute([$ip, $window]);
        $attempts_ip = (int)$stmt->fetchColumn();

        $attempts_email = 0;
        if ($email !== '') {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM login_attempts
                WHERE email = ? AND success = 0 AND attempted_at >= ?
            ");
            $stmt->execute([strtolower(trim($email)), $window]);
            $attempts_email = (int)$stmt->fetchColumn();
        }

        $attempts = max($attempts_ip, $attempts_email);

        if ($attempts >= $this->maxAttempts) {
            $stmt = $this->pdo->prepare("
                SELECT MAX(attempted_at) FROM login_attempts
                WHERE ip = ? AND success = 0 AND attempted_at >= ?
            ");
            $stmt->execute([$ip, $window]);
            $last      = $stmt->fetchColumn();
            $remaining = max(0, $this->lockoutSeconds - (time() - strtotime($last)));

            return ['blocked' => true, 'remaining_seconds' => (int)$remaining, 'attempts' => $attempts];
        }

        return ['blocked' => false, 'remaining_seconds' => 0, 'attempts' => $attempts];
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
?>
