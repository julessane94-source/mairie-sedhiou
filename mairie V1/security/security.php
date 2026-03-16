<?php
/**
 * security.php - Classe de sécurité pour la plateforme
 */

class Security {
    
    /**
     * Générer un token CSRF
     * @return string
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier le token CSRF
     * @param string $token
     * @return bool
     */
    public static function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            self::logSecurityEvent('Tentative CSRF détectée', 'critical');
            return false;
        }
        return true;
    }
    
    /**
     * Afficher un champ CSRF dans les formulaires
     */
    public static function csrfField() {
        $token = self::generateCSRFToken();
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
    
    /**
     * Rate limiting
     * @param string $action
     * @param int $max_attempts
     * @param int $time_window
     * @return bool
     */
    public static function checkRateLimit($action, $max_attempts = 5, $time_window = 900) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'rate_limit_' . $action . '_' . $ip;
        $attempts = 0;
        
        // Utiliser le fichier pour stocker les tentatives
        $file = __DIR__ . '/logs/rate_limit_' . md5($key) . '.tmp';
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['time'] > time() - $time_window) {
                $attempts = $data['attempts'];
            }
        }
        
        if ($attempts >= $max_attempts) {
            self::logSecurityEvent("Rate limit dépassé pour $action", 'warning');
            return false;
        }
        
        $attempts++;
        file_put_contents($file, json_encode([
            'attempts' => $attempts,
            'time' => time()
        ]));
        
        return true;
    }
    
    /**
     * Valider les uploads de fichiers
     * @param array $file
     * @param array $allowed_types
     * @param int $max_size
     * @return array
     */
    public static function validateUpload($file, $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'], $max_size = 5242880) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
        }
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'Fichier trop volumineux (max ' . ($max_size/1024/1024) . ' Mo)'];
        }
        
        // Vérifier le contenu réel
        if (strpos($file['type'], 'image/') === 0) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                return ['success' => false, 'message' => 'Image corrompue ou invalide'];
            }
            
            // Vérifier que le type MIME réel correspond
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $real_mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($real_mime, $allowed_types)) {
                return ['success' => false, 'message' => 'Le type réel du fichier ne correspond pas'];
            }
        }
        
        return ['success' => true, 'message' => 'OK'];
    }
    
    /**
     * Nettoyer les entrées utilisateur
     * @param mixed $input
     * @param string $type
     * @return mixed
     */
    public static function sanitize($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        $input = trim($input);
        
        switch($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Logger les événements de sécurité
     * @param string $message
     * @param string $level
     */
    public static function logSecurityEvent($message, $level = 'info') {
        $log_dir = __DIR__ . '/logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . 'security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = $_SESSION['user_id'] ?? 'anonymous';
        $url = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $log = "[$timestamp] [$level] IP: $ip - User: $user - URL: $url - $message" . PHP_EOL;
        file_put_contents($log_file, $log, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Vérifier la force du mot de passe
     * @param string $password
     * @return array
     */
    public static function checkPasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = '8 caractères minimum';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'une minuscule';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'un caractère spécial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => 5 - count($errors)
        ];
    }
    
    /**
     * Forcer HTTPS
     */
    public static function forceHTTPS() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }
    }
    
    /**
     * Vérifier si l'IP est bannie
     * @return bool
     */
    public static function isIPBanned() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $file = __DIR__ . '/logs/banned_ips.json';
        
        if (file_exists($file)) {
            $banned = json_decode(file_get_contents($file), true) ?: [];
            if (isset($banned[$ip]) && $banned[$ip] > time()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Bannir une IP temporairement
     * @param int $duration
     */
    public static function banIP($duration = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $file = __DIR__ . '/logs/banned_ips.json';
        
        $banned = [];
        if (file_exists($file)) {
            $banned = json_decode(file_get_contents($file), true) ?: [];
        }
        
        $banned[$ip] = time() + $duration;
        file_put_contents($file, json_encode($banned));
        
        self::logSecurityEvent("IP bannie pour $duration secondes", 'critical');
    }
}

// Initialisation de la sécurité
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'IP est bannie
if (Security::isIPBanned()) {
    http_response_code(403);
    die('Accès temporairement bloqué pour cause de tentatives suspectes.');
}
?>