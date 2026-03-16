<?php
/**
 * Logger avancé — Journalisation multi-niveaux (PostgreSQL)
 */

class Logger {

    const LEVEL_INFO     = 'INFO';
    const LEVEL_WARNING  = 'WARNING';
    const LEVEL_ERROR    = 'ERROR';
    const LEVEL_AUDIT    = 'AUDIT';
    const LEVEL_SECURITY = 'SECURITY';

    private ?PDO   $pdo;
    private string $logDir;
    private int    $maxFileSizeBytes;

    public function __construct(?PDO $pdo = null, string $logDir = '', int $maxFileMB = 5) {
        $this->pdo              = $pdo;
        $this->logDir           = $logDir ?: (defined('LOG_PATH') ? LOG_PATH : sys_get_temp_dir());
        $this->maxFileSizeBytes = $maxFileMB * 1024 * 1024;

        if (!is_dir($this->logDir)) mkdir($this->logDir, 0750, true);

        if ($pdo) $this->ensureTable();
    }

    private function ensureTable(): void {
        $this->pdo->exec("
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
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_level ON activity_logs(level)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_user  ON activity_logs(user_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_log_date  ON activity_logs(created_at)");
    }

    public function log(string $level, string $action, string $details = '', ?int $userId = null): void {
        $ip        = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : '';
        $ua        = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $userId    = $userId ?? ($_SESSION['user_id'] ?? null);
        $timestamp = date('Y-m-d H:i:s');

        $this->writeFile($level, $timestamp, $userId, $ip, $action, $details);

        if ($this->pdo && in_array($level, [self::LEVEL_AUDIT, self::LEVEL_SECURITY, self::LEVEL_ERROR], true)) {
            try {
                $this->pdo->prepare("
                    INSERT INTO activity_logs (level, user_id, ip, user_agent, action, details, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ")->execute([$level, $userId, $ip, $ua, $action, $details ?: null, $timestamp]);
            } catch (Exception $e) {
                error_log("Logger BDD error: " . $e->getMessage());
            }
        }
    }

    public function info(string $action, string $details = ''): void     { $this->log(self::LEVEL_INFO,     $action, $details); }
    public function warning(string $action, string $details = ''): void  { $this->log(self::LEVEL_WARNING,  $action, $details); }
    public function error(string $action, string $details = ''): void    { $this->log(self::LEVEL_ERROR,    $action, $details); }
    public function audit(string $action, string $details = ''): void    { $this->log(self::LEVEL_AUDIT,    $action, $details); }
    public function security(string $action, string $details = ''): void { $this->log(self::LEVEL_SECURITY, $action, $details); }

    private function writeFile(string $level, string $ts, ?int $uid, string $ip, string $action, string $details): void {
        $file = $this->logDir . '/' . strtolower($level) . '_' . date('Y-m') . '.log';
        if (file_exists($file) && filesize($file) > $this->maxFileSizeBytes) {
            rename($file, $file . '.' . time() . '.bak');
        }
        $line = sprintf(
            "[%s] [%s] user=%s ip=%s action=%s%s\n",
            $ts, $level, $uid ?? 'anon', $ip, $action,
            $details ? " | " . str_replace(["\r", "\n"], ' ', $details) : ''
        );
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public function getRecent(int $limit = 100, string $level = '', int $userId = 0): array {
        if (!$this->pdo) return [];
        $sql    = "SELECT * FROM activity_logs WHERE 1=1";
        $params = [];
        if ($level)      { $sql .= " AND level = ?";   $params[] = $level; }
        if ($userId > 0) { $sql .= " AND user_id = ?"; $params[] = $userId; }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
